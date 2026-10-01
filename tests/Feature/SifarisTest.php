<?php

namespace Tests\Feature;

use App\Models\SifarisRequest;
use App\Models\SifarisSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SifarisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');
    }

    private function admin(): User
    {
        return tap(User::factory()->create(), fn ($u) => $u->assignRole('admin'));
    }

    private function member(): User
    {
        return tap(User::factory()->create(), fn ($u) => $u->assignRole('user'));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'applicant_name' => 'सीता थापा मगर',
            'parent_relation' => 'छोरी',
            'parent_name' => 'राम बहादुर थापा मगर',
            'grandparent_relation' => 'नातिनी',
            'grandparent_name' => 'हर्क बहादुर थापा मगर',
            'province' => 'लुम्बिनी',
            'district' => 'रोल्पा',
            'municipality' => 'रोल्पा न.पा.',
            'ward_no' => '४',
            'mobile' => '9812345678',
            'email' => 'sita@example.com',
            'photo' => UploadedFile::fake()->image('me.jpg', 350, 450),
            'declaration' => '1',
        ], $overrides);
    }

    private function applyAs(User $user, array $overrides = []): SifarisRequest
    {
        $this->actingAs($user)->post(route('dashboard.my-sifaris.store'), $this->payload($overrides))
            ->assertRedirect(route('dashboard.my-sifaris.index'))
            ->assertSessionHasNoErrors();

        return SifarisRequest::latest('id')->firstOrFail();
    }

    public function test_public_page_and_header_link(): void
    {
        $this->get(route('home'))->assertOk()->assertSee(route('sifaris'), false);
        $this->get(route('sifaris'))->assertOk()->assertSee(route('dashboard.my-sifaris.create'), false);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('dashboard.my-sifaris.create'))->assertRedirect(route('login'));
        $this->post(route('dashboard.my-sifaris.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_can_apply_and_request_is_pending(): void
    {
        $user = $this->member();
        $this->actingAs($user)->get(route('dashboard.my-sifaris.create'))->assertOk();

        $sifaris = $this->applyAs($user);

        $this->assertTrue($sifaris->isPending());
        $this->assertSame($user->id, $sifaris->user_id);
        $this->assertSame('सीता थापा मगर', $sifaris->applicant_name);
        Storage::disk('public')->assertExists(Str::after($sifaris->photo_url, '/storage/'));

        $this->actingAs($user)->get(route('dashboard.my-sifaris.index'))
            ->assertOk()->assertSee('सीता थापा मगर')->assertSee('Under review')
            ->assertDontSee(route('dashboard.sifaris.letter', $sifaris), false);
    }

    public function test_required_fields_and_allowed_relations(): void
    {
        $user = $this->member();

        $this->actingAs($user)->post(route('dashboard.my-sifaris.store'), $this->payload([
            'photo' => null, 'declaration' => null, 'parent_relation' => 'काका', 'province' => 'Nowhere', 'mobile' => 'abc',
        ]))->assertSessionHasErrors(['photo', 'declaration', 'parent_relation', 'province', 'mobile']);

        $this->assertSame(0, SifarisRequest::count());
    }

    public function test_letter_is_only_downloadable_by_owner_after_approval(): void
    {
        $owner = $this->member();
        $sifaris = $this->applyAs($owner);

        $this->actingAs($owner)->get(route('dashboard.sifaris.letter', $sifaris))->assertNotFound();
        $this->actingAs($this->member())->get(route('dashboard.sifaris.letter', $sifaris))->assertForbidden();

        // Admins can preview it before approving.
        $this->actingAs($this->admin())->get(route('dashboard.sifaris.letter', $sifaris))->assertOk()->assertSee('NOT APPROVED');
    }

    public function test_admin_reviews_and_approves_with_letter_details(): void
    {
        $owner = $this->member();
        $sifaris = $this->applyAs($owner);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('dashboard.sifaris.pending'))->assertOk()->assertSee('सीता थापा मगर');
        $this->actingAs($admin)->get(route('dashboard.sifaris.show', $sifaris))
            ->assertOk()->assertSee('राम बहादुर थापा मगर')->assertSee('sifaris-template.jpg', false);

        $this->actingAs($admin)->post(route('dashboard.sifaris.approve', $sifaris), [
            'letter_number' => '२०८३/०८४', 'dispatch_number' => '१२३', 'letter_date' => '२०८३/०६/१४',
        ])->assertRedirect(route('dashboard.sifaris.show', $sifaris));

        $sifaris->refresh();
        $this->assertTrue($sifaris->isApproved());
        $this->assertSame('१२३', $sifaris->dispatch_number);
        $this->assertSame($admin->id, $sifaris->reviewed_by);

        $this->actingAs($owner)->get(route('dashboard.my-sifaris.index'))->assertSee(route('dashboard.sifaris.letter', $sifaris), false);
        $this->actingAs($owner)->get(route('dashboard.sifaris.letter', $sifaris))
            ->assertOk()->assertSee('Download PDF')->assertSee('Download PNG')
            ->assertSee('२०८३/०६/१४')->assertSee('नातिनी')->assertSee($sifaris->photo_url, false)
            ->assertDontSee('NOT APPROVED');

        // Letter details can be corrected later without changing the status.
        $this->actingAs($admin)->put(route('dashboard.sifaris.letter.update', $sifaris), ['letter_number' => '२०८३/०९०'])->assertRedirect();
        $this->assertSame('२०८३/०९०', $sifaris->fresh()->letter_number);
        $this->assertTrue($sifaris->fresh()->isApproved());
    }

    public function test_approving_without_a_date_uses_the_approval_date(): void
    {
        $sifaris = $this->applyAs($this->member());

        $this->travelTo('2026-10-01 12:00:00');
        $this->actingAs($this->admin())->post(route('dashboard.sifaris.approve', $sifaris), ['letter_number' => '१२३'])->assertRedirect();

        $this->assertSame('२०८३/०६/१५', $sifaris->fresh()->letter_date);
    }

    public function test_sifaris_settings_are_edit_only_and_printed_on_the_letter(): void
    {
        $admin = $this->admin();
        $sifaris = $this->applyAs($this->member());

        // Starts with the seeded defaults.
        $this->actingAs($this->member())->get(route('dashboard.sifaris.settings'))->assertForbidden();
        $this->actingAs($admin)->get(route('dashboard.sifaris.settings'))->assertOk()->assertSee('होमराज खमारी मगर');
        $this->actingAs($admin)->get(route('dashboard.sifaris.show', $sifaris))
            ->assertSee('/images/sifaris-signature.png', false)->assertSee('nepalmagarsangh@hotmail.com');

        $this->actingAs($admin)->put(route('dashboard.sifaris.settings.update'), ['signatory_name' => ''])->assertSessionHasErrors('signatory_name');

        $this->actingAs($admin)->put(route('dashboard.sifaris.settings.update'), [
            'signature' => UploadedFile::fake()->image('sign.png', 400, 200),
            'signatory_name' => 'नयाँ महासचिव मगर',
            'signatory_title' => 'अध्यक्ष',
            'phone' => '01-1111111, 9800000000',
            'email' => 'office@example.com',
            'website' => 'example.org.np',
        ])->assertRedirect(route('dashboard.sifaris.settings'));

        $settings = SifarisSetting::sole(); // still the one row
        $this->assertStringStartsWith('/storage/signatures/', $settings->signature_url);
        Storage::disk('public')->assertExists(Str::after($settings->signature_url, '/storage/'));

        $this->actingAs($admin)->get(route('dashboard.sifaris.show', $sifaris))
            ->assertSee($settings->signature_url, false)->assertSee('नयाँ महासचिव मगर')->assertSee('(अध्यक्ष)')
            ->assertSee('9800000000')->assertSee('office@example.com')->assertSee('example.org.np')
            ->assertDontSee('होमराज खमारी मगर');

        // The public sample uses the same details.
        $this->get(route('sifaris'))->assertOk()->assertSee('नयाँ महासचिव मगर');

        // There is no way to add or delete the settings row.
        $this->actingAs($admin)->post('/dashboard/sifaris/settings')->assertStatus(405);
        $this->actingAs($admin)->delete('/dashboard/sifaris/settings')->assertStatus(405);
    }

    public function test_admin_can_disapprove_edit_and_delete(): void
    {
        $owner = $this->member();
        $sifaris = $this->applyAs($owner);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('dashboard.sifaris.reject', $sifaris), ['reason' => 'Photo is not clear.'])->assertRedirect();
        $this->assertTrue($sifaris->fresh()->isRejected());
        $this->actingAs($owner)->get(route('dashboard.my-sifaris.index'))->assertSee('Photo is not clear.');

        $this->actingAs($admin)->get(route('dashboard.sifaris.edit', $sifaris))->assertOk();
        $this->actingAs($admin)->put(route('dashboard.sifaris.update', $sifaris), $this->payload(['photo' => null, 'declaration' => null, 'ward_no' => '५']))
            ->assertRedirect(route('dashboard.sifaris.show', $sifaris));
        $this->assertSame('५', $sifaris->fresh()->ward_no);
        $this->assertSame($sifaris->photo_url, $sifaris->fresh()->photo_url);

        $photo = Str::after($sifaris->photo_url, '/storage/');
        $this->actingAs($admin)->delete(route('dashboard.sifaris.destroy', $sifaris))->assertRedirect(route('dashboard.sifaris.rejected'));
        $this->assertSame(0, SifarisRequest::count());
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_regular_users_cannot_reach_admin_pages(): void
    {
        $sifaris = $this->applyAs($this->member());
        $user = $this->member();

        $this->actingAs($user)->get(route('dashboard.sifaris.pending'))->assertForbidden();
        $this->actingAs($user)->post(route('dashboard.sifaris.approve', $sifaris))->assertForbidden();
        $this->assertTrue($sifaris->fresh()->isPending());
    }

    public function test_sidebar_shows_sifaris_links(): void
    {
        $this->actingAs($this->member())->get(route('dashboard.index'))
            ->assertSee(route('dashboard.my-sifaris.index'), false)
            ->assertDontSee(route('dashboard.sifaris.pending'), false);

        $this->actingAs($this->admin())->get(route('dashboard.index'))
            ->assertSee(route('dashboard.sifaris.pending'), false);
    }
}
