<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\News;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RolePermissionTest extends TestCase
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

    /** A user whose only role holds exactly these permissions. */
    private function staff(array $permissions): User
    {
        $role = Role::create(['name' => 'staff-'.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        return tap(User::factory()->create(), fn ($u) => $u->assignRole($role));
    }

    public function test_every_section_and_action_has_a_permission(): void
    {
        foreach (['news.view', 'news.create', 'news.edit', 'news.delete', 'news.approve', 'messages.view', 'messages.delete', 'membership-applications.approve', 'settings.manage', 'accounting.create', 'roles.edit', 'committee-types.view'] as $name) {
            $this->assertDatabaseHas('permissions', ['name' => $name]);
        }

        $this->assertDatabaseMissing('permissions', ['name' => 'messages.create']);
        $this->assertDatabaseMissing('permissions', ['name' => 'articles.approve']);
    }

    public function test_news_from_staff_waits_for_approval(): void
    {
        $writer = $this->staff(['news.view', 'news.create', 'news.edit']);

        $this->actingAs($writer)->post(route('dashboard.news.store'), ['title' => 'Staff News', 'body' => '<p>x</p>'])
            ->assertRedirect(route('dashboard.news.index'));

        $news = News::where('title', 'Staff News')->firstOrFail();
        $this->assertSame(News::PENDING, $news->status);
        $this->get(route('media.news.index'))->assertDontSee('Staff News');
        $this->get(route('media.news.show', $news))->assertNotFound();

        // Without the approve permission there are no buttons and the routes are closed.
        $this->actingAs($writer)->get(route('dashboard.news.index'))->assertOk()->assertSee('Pending')->assertDontSee('>Approve<', false);
        $this->actingAs($writer)->post(route('dashboard.news.approve', $news->id))->assertForbidden();

        $approver = $this->staff(['news.view', 'news.approve']);
        $this->actingAs($approver)->get(route('dashboard.news.index'))->assertSee('>Approve<', false)->assertSee('>Disapprove<', false);
        $this->actingAs($approver)->post(route('dashboard.news.approve', $news->id))->assertRedirect();

        $news->refresh();
        $this->assertSame(News::APPROVED, $news->status);
        $this->assertSame($approver->id, $news->reviewed_by);
        $this->get(route('media.news.index'))->assertSee('Staff News');
        $this->get(route('media.news.show', $news))->assertOk();

        // Editing by someone who cannot approve sends it back for review.
        $this->actingAs($writer)->put(route('dashboard.news.update', $news->id), ['title' => 'Staff News Edited', 'body' => '<p>y</p>']);
        $this->assertSame(News::PENDING, $news->fresh()->status);

        $this->actingAs($approver)->post(route('dashboard.news.reject', $news->id))->assertRedirect();
        $this->assertSame(News::REJECTED, $news->fresh()->status);
        $this->get(route('media.news.index'))->assertDontSee('Staff News Edited');
    }

    public function test_news_added_by_an_approver_is_published_at_once(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.news.store'), ['title' => 'Admin News', 'body' => '<p>x</p>']);

        $this->assertSame(News::APPROVED, News::where('title', 'Admin News')->value('status'));
    }

    public function test_admin_creates_a_role_and_its_permissions_in_one_step(): void
    {
        $this->actingAs($this->admin())->get(route('dashboard.roles.create'))->assertOk()->assertSee('News')->assertSee('Approve');

        $this->actingAs($this->admin())->post(route('dashboard.roles.store'), [
            'name' => 'News Editor',
            'permissions' => ['news.view', 'news.create', 'news.edit'],
        ])->assertRedirect(route('dashboard.roles.index'));

        $role = Role::findByName('News Editor');
        $this->assertEqualsCanonicalizing(['news.view', 'news.create', 'news.edit'], $role->permissions->pluck('name')->all());
    }

    public function test_role_permissions_must_be_real(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.roles.store'), [
            'name' => 'Bad', 'permissions' => ['news.fly'],
        ])->assertSessionHasErrors('permissions.0');

        $this->actingAs($this->admin())->post(route('dashboard.roles.store'), ['name' => 'admin'])->assertSessionHasErrors('name');
    }

    public function test_staff_can_only_do_what_their_role_allows(): void
    {
        $staff = $this->staff(['news.view', 'news.create']);
        $news = News::factory()->create();

        $this->actingAs($staff)->get(route('dashboard.news.index'))->assertOk()->assertSee('+ Add News')->assertDontSee('>Delete<', false);
        $this->actingAs($staff)->get(route('dashboard.news.create'))->assertOk();
        $this->actingAs($staff)->get(route('dashboard.news.edit', $news->id))->assertForbidden();
        $this->actingAs($staff)->delete(route('dashboard.news.destroy', $news->id))->assertForbidden();
        $this->actingAs($staff)->get(route('dashboard.articles.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('dashboard.settings'))->assertForbidden();
        $this->actingAs($staff)->get(route('dashboard.roles.index'))->assertForbidden();

        $this->assertModelExists($news);
    }

    public function test_sidebar_only_lists_permitted_pages(): void
    {
        $staff = $this->staff(['news.view', 'membership-applications.view']);

        $this->actingAs($staff)->get(route('dashboard.index'))->assertOk()
            ->assertSee('Pending Applications')
            ->assertSee(route('dashboard.news.index'), false)
            ->assertDontSee(route('dashboard.articles.index'), false)
            ->assertDontSee('Site &amp; About Settings', false)
            ->assertDontSee('Permission Matrix')
            ->assertDontSee('Total Users');
    }

    public function test_membership_approve_permission_is_separate_from_view(): void
    {
        $membership = Membership::factory()->create(['status' => Membership::PENDING]);
        $viewer = $this->staff(['membership-applications.view']);

        $this->actingAs($viewer)->get(route('dashboard.membership.show', $membership))->assertOk()->assertDontSee('✔ Approve');
        $this->actingAs($viewer)->post(route('dashboard.membership.approve', $membership))->assertForbidden();

        $approver = $this->staff(['membership-applications.view', 'membership-applications.approve']);
        $this->actingAs($approver)->post(route('dashboard.membership.approve', $membership))->assertRedirect();
        $this->assertSame(Membership::APPROVED, $membership->fresh()->status);
    }

    public function test_livewire_site_settings_needs_permission(): void
    {
        Livewire::actingAs($this->staff(['news.view']))->test('dashboard.settings')->assertForbidden();
        Livewire::actingAs($this->staff(['settings.manage']))->test('dashboard.settings')->assertOk();
    }

    public function test_permission_matrix_updates_every_role_at_once(): void
    {
        $editor = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $user = Role::findByName('user');

        $this->actingAs($this->admin())->get(route('dashboard.permissions.index'))->assertOk()->assertSee('Editor');

        $this->actingAs($this->admin())->put(route('dashboard.permissions.update'), [
            'permissions' => [
                $editor->id => ['news.view', 'news.edit'],
                $user->id => ['events.view'],
            ],
        ])->assertRedirect(route('dashboard.permissions.index'));

        $this->assertEqualsCanonicalizing(['news.view', 'news.edit'], $editor->fresh()->permissions->pluck('name')->all());
        $this->assertSame(['events.view'], $user->fresh()->permissions->pluck('name')->all());
    }

    public function test_built_in_roles_are_protected(): void
    {
        $admin = $this->admin();
        $adminRole = Role::findByName('admin');
        $userRole = Role::findByName('user');

        $this->actingAs($admin)->get(route('dashboard.roles.edit', $adminRole))->assertRedirect(route('dashboard.roles.index'));
        $this->actingAs($admin)->put(route('dashboard.roles.update', $adminRole), ['permissions' => []])->assertForbidden();
        $this->actingAs($admin)->delete(route('dashboard.roles.destroy', $userRole))->assertSessionHas('dashboard-error');

        // The user role keeps its name but can be given permissions.
        $this->actingAs($admin)->put(route('dashboard.roles.update', $userRole), ['name' => 'renamed', 'permissions' => ['events.view']])->assertRedirect();
        $this->assertSame('user', $userRole->fresh()->name);
        $this->assertTrue($userRole->fresh()->hasPermissionTo('events.view'));
    }

    public function test_role_in_use_cannot_be_deleted(): void
    {
        $staff = $this->staff(['news.view']);
        $role = $staff->roles->first();

        $this->actingAs($this->admin())->delete(route('dashboard.roles.destroy', $role))->assertSessionHas('dashboard-error');
        $this->assertModelExists($role);

        $staff->syncRoles('user');
        $this->actingAs($this->admin())->delete(route('dashboard.roles.destroy', $role))->assertSessionHas('dashboard-status');
        $this->assertModelMissing($role);
    }

    public function test_users_can_be_given_custom_roles_but_only_admins_give_admin(): void
    {
        Role::create(['name' => 'news editor', 'guard_name' => 'web']);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('dashboard.users.create'))->assertOk()->assertSee('News Editor');
        $this->actingAs($admin)->post(route('dashboard.users.store'), [
            'name' => 'Ed', 'email' => 'ed@example.com', 'password' => 'secret-pass', 'role' => 'news editor',
        ])->assertRedirect(route('dashboard.users.index'));
        $this->assertTrue(User::where('email', 'ed@example.com')->first()->hasRole('news editor'));

        $manager = $this->staff(['users.view', 'users.create', 'users.edit']);
        $this->actingAs($manager)->post(route('dashboard.users.store'), [
            'name' => 'Sneaky', 'email' => 'sneaky@example.com', 'password' => 'secret-pass', 'role' => Permissions::ADMIN_ROLE,
        ])->assertSessionHasErrors('role');
        $this->actingAs($manager)->put(route('dashboard.users.update', $manager->id), [
            'name' => $manager->name, 'email' => $manager->email, 'role' => Permissions::ADMIN_ROLE,
        ])->assertSessionHasErrors('role');
        $this->assertFalse($manager->fresh()->hasRole(Permissions::ADMIN_ROLE));
    }

    public function test_members_without_permissions_see_the_member_dashboard_only(): void
    {
        $member = tap(User::factory()->create(), fn ($u) => $u->assignRole('user'));

        $this->actingAs($member)->get(route('dashboard.index'))->assertOk()->assertDontSee('Manage content')->assertDontSee('Money collected');
        $this->actingAs($member)->get(route('dashboard.roles.index'))->assertForbidden();
    }
}
