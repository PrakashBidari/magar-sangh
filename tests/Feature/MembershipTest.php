<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');
        Storage::fake('local');
    }

    private function admin(): User
    {
        return tap(User::factory()->create(), fn ($u) => $u->assignRole('admin'));
    }

    private function member(): User
    {
        return tap(User::factory()->create(), fn ($u) => $u->assignRole('user'));
    }

    private function payload(MembershipType $type, array $overrides = []): array
    {
        return array_merge([
            'membership_type_id' => $type->id,
            'full_name' => 'Sita',
            'surname' => 'Thapa Magar',
            'date_of_birth' => '1995-04-12',
            'permanent_address' => 'Liwang, Rolpa',
            'current_address' => 'Kathmandu',
            'province' => 'Lumbini',
            'district' => 'Rolpa',
            'municipality' => 'Rolpa Municipality',
            'ward_no' => 4,
            'mobile' => '9812345678',
            'email' => 'sita@example.com',
            'occupation' => 'Teacher',
            'photo' => UploadedFile::fake()->image('me.jpg'),
            'declaration' => '1',
        ], $overrides);
    }

    public function test_guests_cannot_reach_membership_pages(): void
    {
        $this->get(route('dashboard.my-membership.create'))->assertRedirect(route('login'));
        $this->post(route('dashboard.my-membership.store'), [])->assertRedirect(route('login'));
        $this->get(route('dashboard.membership.pending'))->assertRedirect(route('login'));
    }

    public function test_public_membership_page_lists_open_types_only(): void
    {
        MembershipType::factory()->create(['name_en' => 'Open Type']);
        MembershipType::factory()->create(['name_en' => 'Closed Type', 'is_active' => false]);

        $this->get(route('membership.types'))->assertOk()->assertSee('Open Type')->assertDontSee('Closed Type');
    }

    public function test_member_can_apply_and_it_lands_in_pending(): void
    {
        $type = MembershipType::factory()->create();
        $user = $this->member();

        $this->actingAs($user)->get(route('dashboard.my-membership.create'))->assertOk()->assertSee($type->name_en);

        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($type, [
            'signature' => UploadedFile::fake()->image('sign.png'),
        ]))->assertRedirect(route('dashboard.my-membership.show'));

        $membership = Membership::firstOrFail();
        $this->assertSame($user->id, $membership->user_id);
        $this->assertSame('pending', $membership->status);
        $this->assertNull($membership->membership_number);
        $this->assertTrue($membership->applied_at->isToday());
        $this->assertStringStartsWith('/storage/memberships/photos/', $membership->photo_url);
        $this->assertStringStartsWith('/storage/memberships/signatures/', $membership->signature_url);

        $this->actingAs($user)->get(route('dashboard.my-membership.show'))->assertOk()->assertSee('under review');

        // Only one open application at a time.
        $this->actingAs($user)->get(route('dashboard.my-membership.create'))->assertRedirect(route('dashboard.my-membership.show'));
        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($type))->assertRedirect(route('dashboard.my-membership.show'));
        $this->assertDatabaseCount('memberships', 1);
    }

    public function test_signature_is_optional_but_photo_and_declaration_are_required(): void
    {
        $type = MembershipType::factory()->create();

        $this->actingAs($this->member())
            ->post(route('dashboard.my-membership.store'), $this->payload($type, ['photo' => null, 'declaration' => null]))
            ->assertSessionHasErrors(['photo', 'declaration'])->assertSessionDoesntHaveErrors('signature');

        $this->assertDatabaseCount('memberships', 0);
    }

    public function test_district_must_belong_to_the_province_and_closed_types_are_rejected(): void
    {
        $type = MembershipType::factory()->create();
        $closed = MembershipType::factory()->create(['is_active' => false]);
        $user = $this->member();

        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($type, ['district' => 'Jhapa']))
            ->assertSessionHasErrors('district');
        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($closed))
            ->assertSessionHasErrors('membership_type_id');
    }

    public function test_municipality_must_belong_to_the_selected_district(): void
    {
        $type = MembershipType::factory()->create();
        $user = $this->member();

        // Free text and municipalities of another district are refused.
        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($type, ['municipality' => 'Made Up Place']))
            ->assertSessionHasErrors('municipality');
        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($type, ['municipality' => 'Kathmandu Metropolitan City']))
            ->assertSessionHasErrors('municipality');
        $this->assertDatabaseCount('memberships', 0);

        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($type, ['municipality' => 'Runtigadi Rural Municipality']))
            ->assertSessionDoesntHaveErrors();
        $this->assertSame('Runtigadi Rural Municipality', Membership::firstOrFail()->municipality);
    }

    public function test_address_data_covers_every_district(): void
    {
        $provinces = config('nepal.provinces');

        $this->assertCount(7, $provinces);
        $this->assertSame(77, collect($provinces)->sum(fn ($d) => count($d)));
        $this->assertSame(753, collect($provinces)->flatten()->count());
        $this->assertTrue(collect($provinces)->flatten(1)->every(fn ($m) => count($m) > 0), 'every district has municipalities');
    }

    public function test_price_decides_between_free_and_paid_types(): void
    {
        $free = MembershipType::factory()->create(['fee' => null]);
        $zero = MembershipType::factory()->create(['fee' => 0]);
        $paid = MembershipType::factory()->create(['fee' => 1500]);

        $this->assertSame('Free', $free->fee_label);
        $this->assertSame('Free', $zero->fee_label);
        $this->assertSame('Rs. 1,500.00', $paid->fee_label);
        $this->assertFalse($zero->requiresPayment());
        $this->assertTrue($paid->requiresPayment());
    }

    public function test_voucher_is_required_only_when_the_type_has_a_fee(): void
    {
        $paid = MembershipType::factory()->paid(1000)->create();
        $user = $this->member();

        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($paid))->assertSessionHasErrors('voucher');

        $this->actingAs($user)->post(route('dashboard.my-membership.store'), $this->payload($paid, [
            'voucher' => UploadedFile::fake()->image('slip.jpg'),
        ]))->assertRedirect();

        $membership = Membership::firstOrFail();
        Storage::disk('local')->assertExists($membership->voucher_path);
        $this->assertStringStartsWith('membership-vouchers/', $membership->voucher_path);
    }

    public function test_approving_sets_number_and_expiry_from_the_type_duration(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(10));
        $type = MembershipType::factory()->create(['duration_unit' => 'years', 'duration_value' => 5]);
        $membership = Membership::factory()->create(['membership_type_id' => $type->id]);

        $this->actingAs($this->admin())->post(route('dashboard.membership.approve', $membership))->assertRedirect();

        $membership->refresh();
        $this->assertTrue($membership->isActive());
        $this->assertSame('NMS-'.str_pad((string) $membership->id, 6, '0', STR_PAD_LEFT), $membership->membership_number);
        $this->assertTrue($membership->expires_at->equalTo(now()->addYears(5)));

        // Expires after the plan ends.
        $this->travelTo(now()->addYears(5)->addDay());
        $this->assertTrue($membership->fresh()->isExpired());
        $this->assertFalse($membership->fresh()->isActive());
        $this->assertSame(0, Membership::active()->count());
    }

    public function test_lifetime_membership_never_expires(): void
    {
        $type = MembershipType::factory()->lifetime()->create();
        $membership = Membership::factory()->create(['membership_type_id' => $type->id]);

        $this->actingAs($this->admin())->post(route('dashboard.membership.approve', $membership));

        $membership->refresh();
        $this->assertNull($membership->expires_at);
        $this->travelTo(now()->addYears(50));
        $this->assertTrue($membership->fresh()->isActive());
    }

    public function test_membership_numbers_are_unique_and_never_reused(): void
    {
        $admin = $this->admin();
        $numbers = [];

        foreach (range(1, 3) as $i) {
            $membership = Membership::factory()->create();
            $this->actingAs($admin)->post(route('dashboard.membership.approve', $membership));
            $numbers[] = $membership->fresh()->membership_number;
            $this->actingAs($admin)->delete(route('dashboard.membership.destroy', $membership));
        }

        $this->assertCount(3, array_unique($numbers));
    }

    public function test_approve_and_disapprove_move_applications_between_the_lists(): void
    {
        $admin = $this->admin();
        $membership = Membership::factory()->create(['full_name' => 'Pending Person', 'mobile' => '9800000001']);

        $this->actingAs($admin)->get(route('dashboard.membership.pending'))->assertOk()->assertSee('9800000001');
        $this->actingAs($admin)->get(route('dashboard.membership.approved'))->assertOk()->assertDontSee('9800000001');

        $this->actingAs($admin)->post(route('dashboard.membership.approve', $membership));
        $this->actingAs($admin)->get(route('dashboard.membership.pending'))->assertDontSee('9800000001');
        $this->actingAs($admin)->get(route('dashboard.membership.approved'))->assertSee('9800000001');

        $this->actingAs($admin)->post(route('dashboard.membership.reject', $membership), ['reason' => 'Blurry voucher']);
        $membership->refresh();
        $this->assertSame('rejected', $membership->status);
        $this->assertNull($membership->expires_at);
        $this->actingAs($admin)->get(route('dashboard.membership.approved'))->assertDontSee('9800000001');
        $this->actingAs($admin)->get(route('dashboard.membership.rejected'))->assertSee('9800000001')->assertSee('Blurry voucher');

        // The applicant sees the reason and may apply again.
        $this->actingAs($membership->user)->get(route('dashboard.my-membership.show'))->assertSee('Blurry voucher');
        $this->actingAs($membership->user)->get(route('dashboard.my-membership.create'))->assertOk();

        // A disapproved application can still be approved later, keeping its number rules.
        $this->actingAs($admin)->post(route('dashboard.membership.approve', $membership))->assertRedirect();
        $this->assertTrue($membership->fresh()->isActive());
    }

    public function test_admin_can_view_edit_and_delete_an_application(): void
    {
        $admin = $this->admin();
        $type = MembershipType::factory()->create();
        $membership = Membership::factory()->create();

        $this->actingAs($admin)->get(route('dashboard.membership.show', $membership))->assertOk()->assertSee($membership->displayName());
        $this->actingAs($admin)->get(route('dashboard.membership.edit', $membership))->assertOk();

        $this->actingAs($admin)->put(route('dashboard.membership.update', $membership), $this->payload($type, [
            'full_name' => 'Edited Name', 'photo' => null,
        ]))->assertRedirect(route('dashboard.membership.show', $membership));

        $membership->refresh();
        $this->assertSame('Edited Name', $membership->full_name);
        $this->assertSame($type->id, $membership->membership_type_id);
        $this->assertStringStartsWith('https://picsum.photos', $membership->photo_url, 'photo is kept when no new file is sent');

        $this->actingAs($admin)->delete(route('dashboard.membership.destroy', $membership))->assertRedirect();
        $this->assertDatabaseCount('memberships', 0);
    }

    public function test_only_admins_can_manage_applications(): void
    {
        $membership = Membership::factory()->create();
        $member = $this->member();

        $this->actingAs($member)->get(route('dashboard.membership.pending'))->assertForbidden();
        $this->actingAs($member)->post(route('dashboard.membership.approve', $membership))->assertForbidden();
        $this->actingAs($member)->delete(route('dashboard.membership.destroy', $membership))->assertForbidden();
        $this->actingAs($member)->get(route('dashboard.membership-types.index'))->assertForbidden();
        $this->assertSame('pending', $membership->fresh()->status);
    }

    public function test_id_card_is_only_for_approved_memberships_and_only_owner_or_admin(): void
    {
        $membership = Membership::factory()->create(['full_name' => 'Card Holder']);
        $owner = $membership->user;
        $admin = $this->admin();

        $this->actingAs($owner)->get(route('dashboard.membership.card', $membership))->assertNotFound();

        $membership->load('type')->approve($admin);

        $this->actingAs($owner)->get(route('dashboard.membership.card', $membership))
            ->assertOk()->assertSee('Card Holder')->assertSee($membership->fresh()->membership_number)->assertSee($membership->type->name_en)
            ->assertSee('Authorized signature')
            ->assertSee($membership->municipality.'-'.$membership->ward_no.', '.$membership->district.', '.$membership->province)
            ->assertDontSee($membership->current_address);
        $this->actingAs($admin)->get(route('dashboard.membership.card', $membership))->assertOk();
        $this->actingAs($this->member())->get(route('dashboard.membership.card', $membership))->assertForbidden();
    }

    public function test_vouchers_are_private_to_owner_and_admin(): void
    {
        $paid = MembershipType::factory()->paid()->create();
        $owner = $this->member();
        $this->actingAs($owner)->post(route('dashboard.my-membership.store'), $this->payload($paid, [
            'voucher' => UploadedFile::fake()->image('slip.jpg'),
        ]));
        $membership = Membership::firstOrFail();

        $this->actingAs($owner)->get(route('dashboard.membership.voucher', $membership))->assertOk();
        $this->actingAs($this->admin())->get(route('dashboard.membership.voucher', $membership))->assertOk();
        $this->actingAs($this->member())->get(route('dashboard.membership.voucher', $membership))->assertForbidden();

        $path = $membership->voucher_path;
        $this->actingAs($this->admin())->delete(route('dashboard.membership.destroy', $membership));
        Storage::disk('local')->assertMissing($path);
    }

    public function test_top_bar_shows_the_active_membership_type_only(): void
    {
        $type = MembershipType::factory()->create(['name_en' => 'Gold Circle Member', 'name_np' => 'सुनौलो सदस्य']);
        $membership = Membership::factory()->create(['membership_type_id' => $type->id]);
        $user = $membership->user;

        $this->actingAs($user)->get(route('home'))->assertOk()->assertDontSee('Gold Circle Member');

        $membership->load('type')->approve($this->admin());
        $this->actingAs($user)->get(route('home'))->assertOk()->assertSee('Gold Circle Member')->assertSee('सुनौलो सदस्य');

        $this->travelTo(now()->addYears(6));
        $this->actingAs($user->fresh())->get(route('home'))->assertOk()->assertDontSee('Gold Circle Member');
    }

    public function test_membership_types_are_managed_dynamically(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('dashboard.membership-types.index'))->assertOk();

        $this->actingAs($admin)->post(route('dashboard.membership-types.store'), [
            'name_en' => 'Youth Member', 'name_np' => 'युवा सदस्य', 'duration_unit' => 'years', 'duration_value' => '', 'is_active' => '1',
        ])->assertSessionHasErrors('duration_value');

        $this->actingAs($admin)->post(route('dashboard.membership-types.store'), [
            'name_en' => 'Youth Member', 'name_np' => 'युवा सदस्य', 'duration_unit' => 'months', 'duration_value' => '18', 'fee' => '250', 'is_active' => '1',
        ])->assertRedirect(route('dashboard.membership-types.index'));

        $type = MembershipType::where('name_en', 'Youth Member')->firstOrFail();
        $this->assertSame('18 months', $type->durationLabel());
        $this->assertTrue($type->requiresPayment());

        $this->actingAs($admin)->post(route('dashboard.membership-types.store'), [
            'name_en' => 'Forever Member', 'name_np' => 'सधैं सदस्य', 'duration_unit' => 'lifetime', 'duration_value' => '9', 'is_active' => '1',
        ])->assertRedirect();
        $forever = MembershipType::where('name_en', 'Forever Member')->firstOrFail();
        $this->assertNull($forever->duration_value);
        $this->assertNull($forever->expiryFrom(now()));

        // A type with applications cannot be deleted.
        Membership::factory()->create(['membership_type_id' => $type->id]);
        $this->actingAs($admin)->delete(route('dashboard.membership-types.destroy', $type->id))->assertSessionHas('dashboard-error');
        $this->assertModelExists($type);
        $this->actingAs($admin)->delete(route('dashboard.membership-types.destroy', $forever->id));
        $this->assertModelMissing($forever);
    }

    public function test_dashboard_pages_render_for_admin_and_member(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('dashboard.index'))->assertOk()->assertSee('Pending Applications');
        $this->actingAs($this->member())->get(route('dashboard.index'))->assertOk()->assertSee('Apply for membership');
        $this->actingAs($this->member())->get(route('dashboard.my-membership.show'))->assertOk();
    }
}
