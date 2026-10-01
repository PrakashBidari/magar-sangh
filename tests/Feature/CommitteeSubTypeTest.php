<?php

namespace Tests\Feature;

use App\Models\CommitteeMember;
use App\Models\CommitteeSubType;
use App\Models\CommitteeType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommitteeSubTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return tap(User::factory()->create(), fn ($u) => $u->assignRole('admin'));
    }

    public function test_admin_adds_sub_types_to_a_committee(): void
    {
        $district = CommitteeType::factory()->create(['name_np' => 'जिल्ला समिति', 'name_en' => 'District Committee']);

        $this->actingAs($this->admin())->get(route('dashboard.committee-sub-types.create'))->assertOk()->assertSee('जिल्ला समिति');

        $this->actingAs($this->admin())->post(route('dashboard.committee-sub-types.store'), [
            'committee_type_id' => $district->id, 'name_np' => 'काठमाडौं', 'name_en' => 'Kathmandu', 'sort_order' => 1,
        ])->assertRedirect(route('dashboard.committee-sub-types.index'));

        $this->assertDatabaseHas('committee_sub_types', ['committee_type_id' => $district->id, 'name_en' => 'Kathmandu']);
        $this->actingAs($this->admin())->get(route('dashboard.committee-sub-types.index'))->assertSee('काठमाडौं')->assertSee('जिल्ला समिति');
    }

    public function test_member_form_offers_sub_types_tied_to_their_committee(): void
    {
        $district = CommitteeType::factory()->create(['name_np' => 'जिल्ला समिति']);
        $kathmandu = CommitteeSubType::factory()->create(['committee_type_id' => $district->id, 'name_np' => 'काठमाडौं']);

        $this->actingAs($this->admin())->get(route('dashboard.committee.create'))->assertOk()
            ->assertSee('data-depends-on="f-committee_type_id"', false)
            ->assertSee('data-parent="'.$district->id.'"', false);

        $this->actingAs($this->admin())->post(route('dashboard.committee.store'), [
            'name' => 'Ram Magar', 'phone' => '9841234567', 'committee_type_id' => $district->id, 'committee_sub_type_id' => $kathmandu->id, 'position_np' => 'अध्यक्ष',
        ])->assertSessionHasNoErrors();

        $this->assertSame($kathmandu->id, CommitteeMember::firstOrFail()->committee_sub_type_id);
    }

    public function test_sub_type_must_belong_to_the_chosen_committee(): void
    {
        $central = CommitteeType::factory()->create();
        $kathmandu = CommitteeSubType::factory()->create(); // belongs to another committee

        $this->actingAs($this->admin())->post(route('dashboard.committee.store'), [
            'name' => 'Ram Magar', 'phone' => '9841234567', 'committee_type_id' => $central->id, 'committee_sub_type_id' => $kathmandu->id, 'position_np' => 'अध्यक्ष',
        ])->assertSessionHasErrors('committee_sub_type_id');
    }

    public function test_sub_type_is_optional(): void
    {
        $central = CommitteeType::factory()->create();

        $this->actingAs($this->admin())->post(route('dashboard.committee.store'), [
            'name' => 'Ram Magar', 'phone' => '9841234567', 'committee_type_id' => $central->id, 'position_np' => 'अध्यक्ष',
        ])->assertSessionHasNoErrors();

        $this->assertNull(CommitteeMember::firstOrFail()->committee_sub_type_id);
    }

    public function test_member_phone_number_is_required_and_listed_in_the_dashboard(): void
    {
        $central = CommitteeType::factory()->create();
        $base = ['name' => 'Ram Magar', 'committee_type_id' => $central->id, 'position_np' => 'अध्यक्ष'];

        $this->actingAs($this->admin())->get(route('dashboard.committee.create'))->assertOk()->assertSee('Phone Number')->assertSee('type="tel"', false);

        $this->actingAs($this->admin())->post(route('dashboard.committee.store'), $base)->assertSessionHasErrors('phone');
        $this->actingAs($this->admin())->post(route('dashboard.committee.store'), $base + ['phone' => 'call me'])->assertSessionHasErrors('phone');

        $this->actingAs($this->admin())->post(route('dashboard.committee.store'), $base + ['phone' => '+977 9841-234567'])->assertSessionHasNoErrors();
        $member = CommitteeMember::firstOrFail();
        $this->assertSame('+977 9841-234567', $member->phone);

        // Required on update too, and shown in the dashboard list but not on the public page.
        $this->actingAs($this->admin())->put(route('dashboard.committee.update', $member->id), $base + ['phone' => ''])->assertSessionHasErrors('phone');
        $this->actingAs($this->admin())->get(route('dashboard.committee.index'))->assertSee('+977 9841-234567');
        $this->get(route('about.committee.show', $central))->assertOk()->assertDontSee('9841-234567');
    }

    public function test_sub_type_with_members_cannot_be_deleted(): void
    {
        $sub = CommitteeSubType::factory()->create();
        CommitteeMember::factory()->create(['committee_type_id' => $sub->committee_type_id, 'committee_sub_type_id' => $sub->id]);

        $this->actingAs($this->admin())->delete(route('dashboard.committee-sub-types.destroy', $sub->id))->assertSessionHas('dashboard-error');
        $this->assertModelExists($sub);
    }

    public function test_committee_page_opens_on_the_first_sub_type_tab(): void
    {
        $district = CommitteeType::factory()->create(['name_np' => 'जिल्ला समिति']);
        $kathmandu = CommitteeSubType::factory()->create(['committee_type_id' => $district->id, 'name_np' => 'काठमाडौं', 'sort_order' => 1]);
        $dhading = CommitteeSubType::factory()->create(['committee_type_id' => $district->id, 'name_np' => 'धादिङ', 'name_en' => 'Dhading', 'sort_order' => 2]);
        $unused = CommitteeSubType::factory()->create(['committee_type_id' => $district->id, 'name_np' => 'गोरखा']);

        CommitteeMember::factory()->create(['name' => 'KTM Person', 'committee_type_id' => $district->id, 'committee_sub_type_id' => $kathmandu->id, 'is_current' => true]);
        CommitteeMember::factory()->create(['name' => 'Dhading Person', 'committee_type_id' => $district->id, 'committee_sub_type_id' => $dhading->id, 'is_current' => true]);

        $this->get(route('about.committee.show', $district))->assertOk()
            // No "All" tab: sub types in order, the first one selected; empty sub types get no tab.
            ->assertDontSee('data-sub-tab="all"', false)
            ->assertSeeInOrder(['data-sub-tab="'.$kathmandu->id.'" aria-selected="true"', 'data-sub-tab="'.$dhading->id.'" aria-selected="false"'], false)
            ->assertDontSee('data-sub-tab="'.$unused->id.'"', false)
            // Only the first sub type's members are visible; the others are hidden until their tab is clicked.
            ->assertSee('<div class="card text-center " data-sub="'.$kathmandu->id.'">', false)
            ->assertSee('<div class="card text-center hidden" data-sub="'.$dhading->id.'">', false);
    }

    public function test_committee_without_sub_types_shows_no_tabs(): void
    {
        $central = CommitteeType::factory()->create();
        CommitteeMember::factory()->create(['name' => 'Central Person', 'committee_type_id' => $central->id, 'is_current' => true]);

        $this->get(route('about.committee.show', $central))->assertOk()->assertSee('Central Person')->assertDontSee('data-sub-tab', false);
    }
}
