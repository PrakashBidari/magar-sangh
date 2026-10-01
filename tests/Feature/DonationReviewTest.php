<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonationReviewTest extends TestCase
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
        return tap(User::factory()->create(['name' => 'Sita Magar']), fn ($u) => $u->assignRole('user'));
    }

    private function donationData(array $overrides = []): array
    {
        return $overrides + ['donor_name' => 'Sita Magar', 'amount' => 5000, 'address' => 'Palpa', 'donate_date' => '2026-09-20'];
    }

    public function test_every_user_sees_the_my_donations_menu_and_form(): void
    {
        $member = $this->member();

        $this->actingAs($member)->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('My Donations')
            ->assertSee(route('dashboard.my-donations.create'), false)
            ->assertSee(route('dashboard.my-donations.index'), false);

        $this->actingAs($member)->get(route('dashboard.my-donations.create'))->assertOk()->assertSee('Submit Donation')->assertSee('Sita Magar');
        $this->actingAs($member)->get(route('dashboard.my-donations.index'))->assertOk()->assertSee('No donations yet');
    }

    public function test_qr_and_bank_details_from_settings_are_shown_on_the_apply_page(): void
    {
        $admin = $this->admin();
        $member = $this->member();

        // Nothing set yet: no payment box.
        $this->actingAs($member)->get(route('dashboard.my-donations.create'))
            ->assertOk()->assertSee('Apply For Donation')->assertDontSee('How to donate');

        $this->actingAs($admin)->put(route('dashboard.donation-settings.update'), [
            'donation_qr' => UploadedFile::fake()->create('qr.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('donation_qr');

        $this->actingAs($admin)->put(route('dashboard.donation-settings.update'), [
            'donation_page_content' => '<p>About the fund</p>',
            'donation_qr' => UploadedFile::fake()->image('qr.png', 400, 400),
            'donation_bank_details' => "Nepal Bank Ltd, Kathmandu\nAccount no.: 0123456789",
        ])->assertRedirect(route('dashboard.donation-settings'));

        $settings = Setting::current()->fresh();
        $this->assertStringStartsWith('/storage/donation-qr/', $settings->donation_qr_url);
        Storage::disk('public')->assertExists(Str::after($settings->donation_qr_url, '/storage/'));
        $this->assertSame('<p>About the fund</p>', $settings->donation_page_content);

        $this->actingAs($admin)->get(route('dashboard.donation-settings'))->assertOk()->assertSee($settings->donation_qr_url, false)->assertSee('0123456789');
        $this->actingAs($member)->get(route('dashboard.my-donations.create'))
            ->assertOk()->assertSee('How to donate')->assertSee($settings->donation_qr_url, false)
            ->assertSee('Nepal Bank Ltd, Kathmandu<br />', false)->assertSee('Account no.: 0123456789');

        // Removing the QR keeps the bank details.
        $this->actingAs($admin)->put(route('dashboard.donation-settings.update'), [
            'donation_page_content' => '<p>About the fund</p>',
            'donation_bank_details' => 'Account no.: 0123456789',
            'remove_donation_qr' => '1',
        ]);
        $this->assertNull(Setting::current()->fresh()->donation_qr_url);
        Storage::disk('public')->assertMissing(Str::after($settings->donation_qr_url, '/storage/'));
        $this->actingAs($member)->get(route('dashboard.my-donations.create'))->assertSee('How to donate')->assertDontSee('Scan to pay');
    }

    public function test_full_add_return_resubmit_approve_flow(): void
    {
        $member = $this->member();
        $admin = $this->admin();

        // 1. The member adds a donation with the paid bank voucher (required): it goes to the admin list as pending, not to the site.
        $this->actingAs($member)->post(route('dashboard.my-donations.store'), $this->donationData())
            ->assertSessionHasErrors('voucher_url');

        $this->actingAs($member)->post(route('dashboard.my-donations.store'), $this->donationData(['voucher_url' => UploadedFile::fake()->image('voucher.jpg')]))
            ->assertRedirect(route('dashboard.my-donations.index'));

        $donation = Donation::firstOrFail();
        $this->assertSame(Donation::PENDING, $donation->status);
        $this->assertSame($member->id, $donation->user_id);
        $this->assertStringStartsWith('/storage/donation-vouchers/', $donation->voucher_url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $donation->voucher_url));

        $this->actingAs($admin)->get(route('dashboard.donations.index'))
            ->assertSee('Sita Magar')->assertSee('Pending')->assertSee($donation->voucher_url)->assertSee('>Approve<', false)->assertSee('>Return<', false);
        $this->get(route('donation-list'))->assertDontSee('Palpa');

        // While pending the member can only view it, not edit it.
        $this->actingAs($member)->get(route('dashboard.my-donations.index'))->assertSee('>View<', false)->assertDontSee('Edit &amp; Resubmit', false);
        $this->actingAs($member)->get(route('dashboard.my-donations.show', $donation->id))->assertOk()->assertSee('Waiting for admin review')->assertSee($donation->voucher_url);
        $this->actingAs($member)->get(route('dashboard.my-donations.edit', $donation->id))->assertRedirect(route('dashboard.my-donations.show', $donation->id));
        $this->actingAs($member)->put(route('dashboard.my-donations.update', $donation->id), $this->donationData(['amount' => 1]));
        $this->assertEquals(5000, $donation->fresh()->amount);

        // 2. The admin returns it with a note; the reason is required.
        $this->actingAs($admin)->post(route('dashboard.donations.reject', $donation->id), ['reason' => ''])->assertSessionHasErrors('reason');
        $this->actingAs($admin)->post(route('dashboard.donations.reject', $donation->id), ['reason' => 'Amount should be 500.'])->assertRedirect();

        $donation->refresh();
        $this->assertSame(Donation::RETURNED, $donation->status);
        $this->actingAs($member)->get(route('dashboard.my-donations.index'))->assertSee('Returned')->assertSee('Amount should be 500.')->assertSee('Edit &amp; Resubmit', false);
        $this->actingAs($member)->get(route('dashboard.my-donations.edit', $donation->id))->assertOk()->assertSee('Amount should be 500.');

        // 3. The member fixes it and sends it again.
        $this->actingAs($member)->put(route('dashboard.my-donations.update', $donation->id), $this->donationData(['amount' => 500]))
            ->assertRedirect(route('dashboard.my-donations.index'));

        $donation->refresh();
        $this->assertSame(Donation::PENDING, $donation->status);
        $this->assertEquals(500, $donation->amount);
        $this->actingAs($admin)->get(route('dashboard.donations.index'))->assertSee('Resubmitted');

        // 4. The admin approves it: the member sees it approved, and it is on the site.
        $this->actingAs($admin)->post(route('dashboard.donations.approve', $donation->id))->assertRedirect();

        $donation->refresh();
        $this->assertSame(Donation::APPROVED, $donation->status);
        $this->assertNull($donation->review_note);
        $this->actingAs($member)->get(route('dashboard.my-donations.index'))->assertSee('Approved')->assertDontSee('Edit &amp; Resubmit', false);
        $this->get(route('donation-list'))->assertSee('Palpa');

        // Approved donations are locked for the member.
        $this->actingAs($member)->get(route('dashboard.my-donations.edit', $donation->id))->assertRedirect(route('dashboard.my-donations.show', $donation->id));
        $this->actingAs($member)->put(route('dashboard.my-donations.update', $donation->id), $this->donationData(['amount' => 99999]));
        $this->assertEquals(500, $donation->fresh()->amount);
    }

    public function test_users_only_see_and_edit_their_own_donations(): void
    {
        $other = Donation::factory()->create(['user_id' => User::factory()->create()->id, 'status' => Donation::PENDING, 'donor_name' => 'Someone Else']);
        $member = $this->member();

        $this->actingAs($member)->get(route('dashboard.my-donations.index'))->assertDontSee('Someone Else');
        $this->actingAs($member)->get(route('dashboard.my-donations.edit', $other->id))->assertNotFound();
        $this->actingAs($member)->put(route('dashboard.my-donations.update', $other->id), $this->donationData())->assertNotFound();
    }

    public function test_approving_needs_the_donations_approve_permission(): void
    {
        $donation = Donation::factory()->create(['status' => Donation::PENDING]);

        $viewer = User::factory()->create();
        $viewer->assignRole(tap(Role::create(['name' => 'donation viewer', 'guard_name' => 'web']))->syncPermissions(['donations.view']));

        $this->actingAs($viewer)->get(route('dashboard.donations.index'))->assertOk()->assertDontSee('>Approve<', false);
        $this->actingAs($viewer)->post(route('dashboard.donations.approve', $donation->id))->assertForbidden();
        $this->actingAs($viewer)->post(route('dashboard.donations.reject', $donation->id), ['reason' => 'x'])->assertForbidden();
        $this->assertSame(Donation::PENDING, $donation->fresh()->status);
    }

    public function test_totals_only_count_approved_donations(): void
    {
        Donation::factory()->create(['amount' => 1000]);
        Donation::factory()->create(['amount' => 7000, 'status' => Donation::PENDING]);

        $this->actingAs($this->admin())->get(route('dashboard.donations.index'))->assertSee('Rs. 1,000.00')->assertDontSee('Rs. 8,000.00');
    }

    public function test_donation_added_by_an_admin_is_approved_at_once(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.donations.store'), $this->donationData());

        $this->assertSame(Donation::APPROVED, Donation::firstOrFail()->status);
    }
}
