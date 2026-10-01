<?php

namespace Tests\Feature;

use App\Models\AccountingCategory;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingCategoryTest extends TestCase
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

    private function entry(array $overrides = []): array
    {
        return $overrides + [
            'date' => '2026-09-10', 'type' => 'income', 'category' => 'Hall Rent Income',
            'description' => 'Hall booking', 'amount' => '1000', 'payment_method' => 'Cash',
        ];
    }

    public function test_starting_categories_are_copied_from_the_old_config_list(): void
    {
        $this->assertDatabaseHas('accounting_categories', ['type' => 'income', 'name' => 'Donation', 'is_active' => true]);
        $this->assertDatabaseHas('accounting_categories', ['type' => 'expense', 'name' => 'Rent']);
    }

    public function test_entry_categories_submenu_and_crud(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('dashboard.accounting.index'))
            ->assertSee('Entry Categories')->assertSee(route('dashboard.accounting-categories.index'), false);

        $this->actingAs($admin)->get(route('dashboard.accounting-categories.create'))->assertOk();
        $this->actingAs($admin)->post(route('dashboard.accounting-categories.store'), [
            'name' => 'Hall Rent Income', 'type' => 'income', 'sort_order' => 3, 'is_active' => 1,
        ])->assertRedirect(route('dashboard.accounting-categories.index'));

        // The same name twice for one type is refused, but is fine for the other type.
        $this->actingAs($admin)->post(route('dashboard.accounting-categories.store'), ['name' => 'Hall Rent Income', 'type' => 'income'])
            ->assertSessionHasErrors('name');
        $this->actingAs($admin)->post(route('dashboard.accounting-categories.store'), ['name' => 'Hall Rent Income', 'type' => 'expense'])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)->get(route('dashboard.accounting-categories.index'))->assertSee('Hall Rent Income');
    }

    public function test_new_categories_show_on_the_entry_form_and_are_accepted(): void
    {
        $admin = $this->admin();
        AccountingCategory::create(['type' => 'income', 'name' => 'Hall Rent Income']);

        $this->actingAs($admin)->get(route('dashboard.accounting.create'))->assertOk()->assertSee('Hall Rent Income');

        $this->actingAs($admin)->post(route('dashboard.accounting.store'), $this->entry())->assertSessionHasNoErrors();
        $this->assertDatabaseHas('transactions', ['category' => 'Hall Rent Income']);

        // A name that is not an income category is refused.
        $this->actingAs($admin)->post(route('dashboard.accounting.store'), $this->entry(['category' => 'Made Up']))->assertSessionHasErrors('category');
    }

    public function test_inactive_categories_are_hidden_from_the_form(): void
    {
        AccountingCategory::create(['type' => 'income', 'name' => 'Old Income', 'is_active' => false]);

        $this->actingAs($this->admin())->get(route('dashboard.accounting.create'))->assertDontSee('Old Income');
        $this->actingAs($this->admin())->post(route('dashboard.accounting.store'), $this->entry(['category' => 'Old Income']))->assertSessionHasErrors('category');
    }

    public function test_renaming_updates_entries_and_used_categories_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $category = AccountingCategory::create(['type' => 'income', 'name' => 'Hall Rent Income']);
        $this->actingAs($admin)->post(route('dashboard.accounting.store'), $this->entry());

        $this->actingAs($admin)->put(route('dashboard.accounting-categories.update', $category->id), [
            'name' => 'Hall Booking', 'type' => 'income', 'sort_order' => 0, 'is_active' => 1,
        ])->assertRedirect(route('dashboard.accounting-categories.index'));

        $this->assertSame('Hall Booking', Transaction::firstOrFail()->category);

        $this->actingAs($admin)->delete(route('dashboard.accounting-categories.destroy', $category->id))
            ->assertSessionHas('dashboard-error');
        $this->assertModelExists($category);

        Transaction::query()->delete();
        $this->actingAs($admin)->delete(route('dashboard.accounting-categories.destroy', $category->id));
        $this->assertModelMissing($category);
    }

    public function test_managing_categories_needs_its_own_permission(): void
    {
        $role = Role::create(['name' => 'bookkeeper', 'guard_name' => 'web']);
        $role->syncPermissions(['accounting.view', 'accounting.create']);
        $bookkeeper = tap(User::factory()->create(), fn ($u) => $u->assignRole($role));

        $this->actingAs($bookkeeper)->get(route('dashboard.accounting.create'))->assertOk()->assertDontSee('Manage Entry Categories');
        $this->actingAs($bookkeeper)->get(route('dashboard.accounting-categories.index'))->assertForbidden();
    }
}
