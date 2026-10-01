<?php

namespace Tests\Feature;

use App\Models\CommitteeMember;
use App\Models\CommitteeType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommitteeTypeTest extends TestCase
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

    public function test_admin_can_create_a_committee_type(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.committee-types.store'), [
            'name_np' => 'सल्लाहकार समिति', 'name_en' => 'Advisory Committee',
        ])->assertRedirect(route('dashboard.committee-types.index'));

        $this->assertDatabaseHas('committee_types', ['name_en' => 'Advisory Committee', 'sort_order' => 0]);
    }

    public function test_member_form_lists_committee_types_and_saves_the_choice(): void
    {
        $admin = $this->admin();
        $type = CommitteeType::factory()->create(['name_np' => 'सल्लाहकार समिति']);

        $this->actingAs($admin)->get(route('dashboard.committee.create'))
            ->assertOk()->assertSee('सल्लाहकार समिति')->assertSee('— Select committee —');

        $this->actingAs($admin)->post(route('dashboard.committee.store'), [
            'name' => 'Advisor One', 'phone' => '9841234567', 'position_np' => 'सल्लाहकार', 'committee_type_id' => $type->id, 'is_current' => '1',
        ])->assertRedirect();

        $member = CommitteeMember::firstOrFail();
        $this->assertSame($type->id, $member->committee_type_id);
        $this->assertFalse($member->show_on_homepage, 'Members must not be on the homepage unless chosen.');

        $this->actingAs($admin)->get(route('dashboard.committee.index'))->assertOk()->assertSee('सल्लाहकार समिति');
    }

    public function test_member_committee_is_required(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.committee.store'), [
            'name' => 'X', 'position_np' => 'सदस्य',
        ])->assertSessionHasErrors('committee_type_id');
    }

    public function test_member_committee_must_exist(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.committee.store'), [
            'name' => 'X', 'position_np' => 'सदस्य', 'committee_type_id' => 999,
        ])->assertSessionHasErrors('committee_type_id');
    }

    public function test_committee_type_with_members_cannot_be_deleted(): void
    {
        $type = CommitteeType::factory()->create();
        CommitteeMember::factory()->create(['committee_type_id' => $type->id]);

        $this->actingAs($this->admin())->delete(route('dashboard.committee-types.destroy', $type->id))
            ->assertSessionHas('dashboard-error');

        $this->assertDatabaseHas('committee_types', ['id' => $type->id]);
    }

    public function test_committee_index_lets_visitors_pick_a_committee(): void
    {
        $advisory = CommitteeType::factory()->create(['name_np' => 'सल्लाहकार समिति', 'sort_order' => 2]);
        $executive = CommitteeType::factory()->create(['name_np' => 'कार्य समिति', 'sort_order' => 1]);
        $empty = CommitteeType::factory()->create(['name_np' => 'खाली समिति']);
        CommitteeMember::factory()->create(['name' => 'Advisor Person', 'committee_type_id' => $advisory->id, 'is_current' => true]);
        CommitteeMember::factory()->create(['name' => 'Executive Person', 'committee_type_id' => $executive->id, 'is_current' => true]);

        $this->get(route('about.committee'))->assertOk()
            ->assertSeeInOrder(['कार्य समिति', 'सल्लाहकार समिति'])
            ->assertSee(route('about.committee.show', $executive), false)
            ->assertDontSee('खाली समिति')
            ->assertDontSee('Executive Person'); // members are on each committee's own page
    }

    public function test_each_committee_has_its_own_page_with_present_then_past_members(): void
    {
        $type = CommitteeType::factory()->create(['name_np' => 'कार्य समिति']);
        $other = CommitteeType::factory()->create(['name_np' => 'अर्को समिति']);
        CommitteeMember::factory()->create(['name' => 'Now Person', 'committee_type_id' => $type->id, 'is_current' => true]);
        CommitteeMember::factory()->create(['name' => 'Former Person', 'committee_type_id' => $type->id, 'is_current' => false, 'term_label' => '२०७५ – २०७९']);
        CommitteeMember::factory()->create(['name' => 'Other Person', 'committee_type_id' => $other->id]);

        $this->get(route('about.committee.show', $type))->assertOk()
            ->assertSee('<h1 class="section-title-np">कार्य समिति</h1>', false)
            ->assertSeeInOrder(['Now Person', 'Past Members', 'Former Person', '२०७५ – २०७९'])
            ->assertDontSee('Other Person');
    }

    public function test_homepage_shows_only_members_marked_for_it(): void
    {
        CommitteeMember::factory()->create(['name' => 'Shown Person', 'show_on_homepage' => true]);
        CommitteeMember::factory()->create(['name' => 'Hidden Person', 'show_on_homepage' => false]);

        $this->get(route('home'))->assertOk()->assertSee('Shown Person')->assertDontSee('Hidden Person');
    }
}
