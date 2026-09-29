<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'date' => '2026-09-10',
            'type' => 'income',
            'category' => 'Donation',
            'description' => 'Donation for the annual event',
            'amount' => '2500.50',
            'payment_method' => 'eSewa',
            'reference_no' => 'ESW-1001',
            'remarks' => 'Received by treasurer',
        ];
    }

    public function test_accounting_is_admin_only(): void
    {
        $this->get(route('dashboard.accounting.index'))->assertRedirect(route('login'));

        $member = tap(User::factory()->create(), fn ($u) => $u->assignRole('user'));
        $this->actingAs($member)->get(route('dashboard.accounting.index'))->assertForbidden();
        $this->actingAs($member)->post(route('dashboard.accounting.store'), $this->payload())->assertForbidden();
    }

    public function test_sidebar_and_pages_render(): void
    {
        $admin = $this->admin();
        $entry = Transaction::factory()->create();

        $this->actingAs($admin)->get(route('dashboard.index'))->assertOk()->assertSee('Accounting');
        $this->actingAs($admin)->get(route('dashboard.accounting.index'))->assertOk()->assertSee($entry->description);
        $this->actingAs($admin)->get(route('dashboard.accounting.create'))->assertOk();
        $this->actingAs($admin)->get(route('dashboard.accounting.edit', $entry))->assertOk();
    }

    public function test_entry_can_be_created_updated_and_deleted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('dashboard.accounting.store'), $this->payload())
            ->assertRedirect(route('dashboard.accounting.index', ['type' => 'income']));

        $entry = Transaction::firstOrFail();
        $this->assertSame('2500.50', $entry->amount);
        $this->assertSame($admin->id, $entry->created_by);

        $this->actingAs($admin)->put(route('dashboard.accounting.update', $entry), $this->payload([
            'type' => 'expense', 'category' => 'Rent', 'amount' => '900', 'payment_method' => 'Cash',
        ]))->assertRedirect(route('dashboard.accounting.index', ['type' => 'expense']));

        $this->assertSame('expense', $entry->fresh()->type);
        $this->assertSame('Rent', $entry->fresh()->category);

        $this->actingAs($admin)->delete(route('dashboard.accounting.destroy', $entry))->assertRedirect();
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_validation_rejects_bad_input(): void
    {
        $admin = $this->admin();

        // Category must belong to the chosen type, amount must be positive.
        $this->actingAs($admin)->post(route('dashboard.accounting.store'), $this->payload(['category' => 'Rent', 'amount' => '0']))
            ->assertSessionHasErrors(['category', 'amount']);

        $this->actingAs($admin)->post(route('dashboard.accounting.store'), $this->payload(['type' => 'loan']))
            ->assertSessionHasErrors('type');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_an_entry_keeps_a_category_that_was_removed_from_the_lists(): void
    {
        $admin = $this->admin();
        $entry = Transaction::factory()->income()->create(['category' => 'Legacy Category']);

        $this->actingAs($admin)->put(route('dashboard.accounting.update', $entry), $this->payload(['category' => 'Legacy Category']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Legacy Category', $entry->fresh()->category);
    }

    public function test_list_filters_by_type_dates_checkboxes_and_search(): void
    {
        $admin = $this->admin();
        Transaction::factory()->create(['type' => 'income', 'category' => 'Sales', 'payment_method' => 'Cash', 'date' => '2026-09-01', 'description' => 'Alpha sale', 'amount' => 1000]);
        Transaction::factory()->create(['type' => 'income', 'category' => 'Donation', 'payment_method' => 'Bank', 'date' => '2026-09-05', 'description' => 'Beta gift', 'amount' => 2000]);
        Transaction::factory()->create(['type' => 'expense', 'category' => 'Rent', 'payment_method' => 'Cash', 'date' => '2026-09-08', 'description' => 'Gamma rent', 'amount' => 400]);
        Transaction::factory()->create(['type' => 'expense', 'category' => 'Food', 'payment_method' => 'Khalti', 'date' => '2026-10-01', 'description' => 'Delta lunch', 'amount' => 100]);

        $descriptions = fn ($query) => $this->actingAs($admin)->get(route('dashboard.accounting.index', $query))->assertOk()
            ->viewData('rows')->pluck('description')->sort()->values()->all();

        $this->assertSame(['Alpha sale', 'Beta gift'], $descriptions(['type' => 'income']));
        $this->assertSame(['Alpha sale', 'Gamma rent'], $descriptions(['methods' => ['Cash']]));
        $this->assertSame(['Alpha sale', 'Gamma rent'], $descriptions(['categories' => ['Sales', 'Rent']]));
        $this->assertSame(['Beta gift', 'Gamma rent'], $descriptions(['from' => '2026-09-02', 'to' => '2026-09-30']));
        $this->assertSame(['Delta lunch'], $descriptions(['q' => 'lunch']));

        // Totals ignore the Income / Expense tab so the cards stay complete.
        $view = $this->actingAs($admin)->get(route('dashboard.accounting.index', ['type' => 'income', 'to' => '2026-09-30']))->assertOk();
        $this->assertSame(3000.0, $view->viewData('income'));
        $this->assertSame(400.0, $view->viewData('expense'));
        $this->assertSame(2, $view->viewData('counts')['income']);
        $this->assertSame(3, $view->viewData('counts')['all']);
    }

    public function test_export_payload_matches_the_filtered_rows(): void
    {
        $admin = $this->admin();
        Transaction::factory()->income()->create(['description' => 'Keep me', 'amount' => 10]);
        Transaction::factory()->expense()->create(['description' => 'Drop me', 'amount' => 5]);

        $export = $this->actingAs($admin)->get(route('dashboard.accounting.index', ['type' => 'income']))
            ->viewData('export');

        $this->assertCount(1, $export);
        $this->assertSame('Keep me', $export[0]['description']);
        $this->assertSame('Income', $export[0]['type']);
    }
}
