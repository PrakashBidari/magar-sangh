<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A category offered on the income / expense form. Entries store the category name as text. */
class AccountingCategory extends Model
{
    protected $fillable = ['type', 'name', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** Entries in the book that use this category. */
    public function transactions(): Builder
    {
        return Transaction::query()->where('type', $this->type)->where('category', $this->name);
    }

    /**
     * Active category names per type, in display order.
     *
     * @return array{income: list<string>, expense: list<string>}
     */
    public static function grouped(): array
    {
        $names = static::query()->active()->orderBy('sort_order')->orderBy('name')->get(['type', 'name'])
            ->groupBy('type')->map(fn ($rows) => $rows->pluck('name')->all());

        return [
            Transaction::INCOME => $names[Transaction::INCOME] ?? [],
            Transaction::EXPENSE => $names[Transaction::EXPENSE] ?? [],
        ];
    }
}
