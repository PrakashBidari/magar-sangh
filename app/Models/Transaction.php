<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One line of the simple daily income & expense book. */
class Transaction extends Model
{
    use HasFactory;

    public const INCOME = 'income';
    public const EXPENSE = 'expense';

    protected $fillable = ['date', 'type', 'category', 'description', 'amount', 'payment_method', 'reference_no', 'remarks', 'created_by'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isIncome(): bool
    {
        return $this->type === self::INCOME;
    }

    /**
     * Apply the list filters: type, from / to date, category[] and method[] checkboxes, and free-text search.
     *
     * @param  array{type?: ?string, from?: ?string, to?: ?string, categories?: array, methods?: array, q?: ?string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('date', '<=', $to))
            ->when($filters['categories'] ?? [], fn ($q, $categories) => $q->whereIn('category', $categories))
            ->when($filters['methods'] ?? [], fn ($q, $methods) => $q->whereIn('payment_method', $methods))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';

                $q->where(fn ($w) => $w->where('description', 'like', $like)
                    ->orWhere('reference_no', 'like', $like)
                    ->orWhere('remarks', 'like', $like));
            });
    }
}
