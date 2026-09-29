<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\DonationDocument;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MoneyCollectedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');
        $this->admin = tap(User::factory()->create(), fn ($u) => $u->assignRole('admin'));
    }

    public function test_donation_page_content_is_managed_from_the_dashboard_and_shown_above_the_list(): void
    {
        $this->actingAs(tap(User::factory()->create(), fn ($u) => $u->assignRole('user')))
            ->get(route('dashboard.donation-settings'))->assertForbidden();

        $this->actingAs($this->admin)->get(route('dashboard.donation-settings'))->assertOk();
        $this->actingAs($this->admin)->get(route('dashboard.donations.index'))->assertOk()->assertSee('Lakhan Thapa Pratisthan');

        $html = '<p>About the foundation</p><p><img src="/storage/editor/photo.jpg"></p><p><a href="/storage/editor/report.pdf">📄 report.pdf</a></p>';
        $this->actingAs($this->admin)->put(route('dashboard.donation-settings.update'), ['donation_page_content' => $html])
            ->assertRedirect(route('dashboard.donation-settings'));

        $this->assertSame($html, Setting::current()->fresh()->donation_page_content);
        $this->get(route('donation-list'))->assertOk()
            ->assertSeeInOrder(['About the foundation', '/storage/editor/report.pdf', 'Top Donors'], false);
    }

    public function test_titled_pdfs_are_uploaded_listed_with_download_and_deleted(): void
    {
        $this->actingAs($this->admin)->post(route('dashboard.donation-settings.documents.store'), [
            'title' => '', 'pdf' => UploadedFile::fake()->image('not-a-pdf.jpg'),
        ])->assertSessionHasErrors(['title', 'pdf']);

        $this->actingAs($this->admin)->post(route('dashboard.donation-settings.documents.store'), [
            'title' => 'Annual Report 2082', 'pdf' => UploadedFile::fake()->create('report.pdf', 300, 'application/pdf'),
        ])->assertRedirect(route('dashboard.donation-settings'));

        $document = DonationDocument::firstOrFail();
        Storage::disk('public')->assertExists(Str::after($document->file_url, '/storage/'));

        $this->actingAs($this->admin)->get(route('dashboard.donation-settings'))->assertSee('Annual Report 2082');
        $this->get(route('donation-list'))->assertOk()
            ->assertSee('Annual Report 2082')
            ->assertSee('href="'.$document->file_url.'" target="_blank"', false)
            ->assertSee('Download');

        $this->actingAs($this->admin)->delete(route('dashboard.donation-settings.documents.destroy', $document))->assertRedirect();
        $this->assertModelMissing($document);
        Storage::disk('public')->assertMissing(Str::after($document->file_url, '/storage/'));
        $this->get(route('donation-list'))->assertDontSee('Annual Report 2082');
    }

    public function test_editor_accepts_images_and_pdfs_only(): void
    {
        $upload = fn ($file) => $this->actingAs($this->admin)->postJson(route('dashboard.editor-upload'), ['upload' => $file]);

        $upload(UploadedFile::fake()->create('report.pdf', 200, 'application/pdf'))->assertOk()->assertJsonPath('url', fn ($url) => str_ends_with($url, '.pdf'));
        $upload(UploadedFile::fake()->image('photo.jpg'))->assertOk();
        $upload(UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream'))->assertUnprocessable();
        $upload(UploadedFile::fake()->create('big.pdf', 11000, 'application/pdf'))->assertUnprocessable();
    }

    public function test_totals_are_shown_on_the_lists_and_the_overview(): void
    {
        Donation::factory()->create(['amount' => 1500]);
        Donation::factory()->create(['amount' => 2500]);

        $paid = MembershipType::factory()->create(['fee' => 1000]);
        $free = MembershipType::factory()->create(['fee' => 0]);
        Membership::factory()->create(['membership_type_id' => $paid->id])->load('type')->approve($this->admin);
        Membership::factory()->create(['membership_type_id' => $free->id])->load('type')->approve($this->admin);
        Membership::factory()->create(['membership_type_id' => $paid->id]); // pending: not collected
        $rejected = Membership::factory()->create(['membership_type_id' => $paid->id])->load('type');
        $rejected->approve($this->admin);
        $rejected->reject($this->admin);

        // A later price change does not rewrite what was already collected.
        $paid->update(['fee' => 5000]);
        $this->assertSame(1000.0, Membership::totalCollected());

        Transaction::factory()->create(['type' => Transaction::INCOME, 'amount' => 700]);
        Transaction::factory()->create(['type' => Transaction::EXPENSE, 'amount' => 200]);

        // The total is for admins only, never on the public page.
        $this->get(route('donation-list'))->assertOk()->assertDontSee('Total Donation Collected')->assertDontSee('Rs. 4,000.00');
        $this->actingAs($this->admin)->get(route('dashboard.donations.index'))->assertSee('Total Donation Collected')->assertSee('Rs. 4,000.00');
        $this->actingAs($this->admin)->get(route('dashboard.membership.pending'))->assertSee('Total Membership Fees Collected')->assertSee('Rs. 1,000.00');

        $this->actingAs($this->admin)->get(route('dashboard.index'))->assertOk()
            ->assertSee('Money collected')
            ->assertSeeInOrder(['Lakhan Thapa Pratisthan Donations', 'Rs. 4,000.00', 'Membership Fees', 'Rs. 1,000.00', 'Accounting Income', 'Rs. 700.00', 'Accounting Expense', 'Rs. 200.00', 'Accounting Balance', 'Rs. 500.00']);
    }
}
