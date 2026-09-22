<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Simple daily income & expense book: list with filters (and PDF / Excel export in the browser), add, edit, delete. */
class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'type' => ['nullable', Rule::in([Transaction::INCOME, Transaction::EXPENSE])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string', 'max:100'],
            'methods' => ['nullable', 'array'],
            'methods.*' => ['string', 'max:50'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $filters = [
            'type' => $request->query('type'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'categories' => array_values((array) $request->query('categories', [])),
            'methods' => array_values((array) $request->query('methods', [])),
            'q' => trim((string) $request->query('q', '')),
        ];

        // Tab counts and totals ignore the Income / Expense tab so all three stay visible.
        $totals = Transaction::query()->filter(['type' => null] + $filters)
            ->selectRaw('type, count(*) as entries, sum(amount) as total')->groupBy('type')->get()->keyBy('type');

        $income = (float) ($totals[Transaction::INCOME]->total ?? 0);
        $expense = (float) ($totals[Transaction::EXPENSE]->total ?? 0);

        $rows = Transaction::query()->filter($filters)->orderByDesc('date')->orderByDesc('id')->get();

        return view('dashboard.accounting.index', [
            'rows' => $rows,
            'filters' => $filters,
            'counts' => [
                'all' => (int) $totals->sum('entries'),
                Transaction::INCOME => (int) ($totals[Transaction::INCOME]->entries ?? 0),
                Transaction::EXPENSE => (int) ($totals[Transaction::EXPENSE]->entries ?? 0),
            ],
            'income' => $income,
            'expense' => $expense,
            'categoryOptions' => $this->categoryOptions($filters['type']),
            'methodOptions' => $this->methodOptions(),
            'export' => $rows->map(fn (Transaction $t) => [
                'date' => $t->date->format('Y-m-d'),
                'type' => ucfirst($t->type),
                'category' => $t->category,
                'description' => $t->description,
                'amount' => (float) $t->amount,
                'method' => $t->payment_method,
                'reference' => $t->reference_no ?? '',
                'remarks' => $t->remarks ?? '',
            ])->values(),
        ]);
    }

    public function create(Request $request): View
    {
        $type = $request->query('type') === Transaction::EXPENSE ? Transaction::EXPENSE : Transaction::INCOME;

        return $this->form(new Transaction(['type' => $type, 'date' => now(), 'payment_method' => 'Cash']));
    }

    public function store(Request $request): RedirectResponse
    {
        $transaction = Transaction::create($this->validated($request) + ['created_by' => $request->user()->id]);

        return redirect()->route('dashboard.accounting.index', ['type' => $transaction->type])
            ->with('dashboard-status', ucfirst($transaction->type).' entry added.');
    }

    public function edit(Transaction $transaction): View
    {
        return $this->form($transaction);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($this->validated($request, $transaction));

        return redirect()->route('dashboard.accounting.index', ['type' => $transaction->type])
            ->with('dashboard-status', 'Entry updated.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()->route('dashboard.accounting.index', ['type' => $transaction->type])
            ->with('dashboard-status', 'Entry deleted.');
    }

    private function form(Transaction $transaction): View
    {
        return view('dashboard.accounting.form', [
            'transaction' => $transaction,
            'categories' => config('accounting.categories'),
            'methods' => $this->methodOptions($transaction->payment_method),
        ]);
    }

    private function validated(Request $request, ?Transaction $existing = null): array
    {
        $type = $request->input('type');
        // An entry may keep a category that was later removed from the config lists.
        $allowed = array_merge(config('accounting.categories.'.$type, []), array_filter([$existing?->category]));

        return $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', Rule::in([Transaction::INCOME, Transaction::EXPENSE])],
            'category' => ['required', 'string', 'max:100', Rule::in($allowed)],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'payment_method' => ['required', 'string', 'max:50', Rule::in($this->methodOptions($existing?->payment_method))],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /** Payment methods from config, plus the one an existing entry already uses. */
    private function methodOptions(?string $current = null): array
    {
        return array_values(array_unique(array_filter([
            ...config('accounting.payment_methods'),
            ...Transaction::query()->distinct()->pluck('payment_method')->all(),
            $current,
        ])));
    }

    /** Category checkboxes for the filter: config lists plus any category already used in the book. */
    private function categoryOptions(?string $type): array
    {
        $configured = $type ? config('accounting.categories.'.$type) : array_merge(...array_values(config('accounting.categories')));
        $used = Transaction::query()->when($type, fn ($q) => $q->where('type', $type))->distinct()->pluck('category')->all();

        return array_values(array_unique([...$configured, ...$used]));
    }
}
