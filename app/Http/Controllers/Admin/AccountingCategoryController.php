<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Accounting entry categories: plain dashboard CRUD, kept in step with the entries that use them. */
class AccountingCategoryController extends ResourceController
{
    protected function rules(?Model $model): array
    {
        $rules = parent::rules($model);

        // The same name cannot appear twice for one type.
        $rules['name'][] = Rule::unique('accounting_categories', 'name')
            ->where('type', request()->input('type'))
            ->ignore($model?->getKey());

        return $rules;
    }

    /** Renaming a category also renames it on the entries filed under it (their income / expense type never changes). */
    protected function persist(Request $request, ?Model $model): Model
    {
        $before = $model?->only(['type', 'name']);

        $category = parent::persist($request, $model);

        if ($before && $before['type'] === $category->type && $before['name'] !== $category->name) {
            Transaction::query()
                ->where('type', $before['type'])->where('category', $before['name'])
                ->update(['category' => $category->name]);
        }

        return $category;
    }

    protected function deleteBlockedReason(Model $model): ?string
    {
        $used = $model->transactions()->count();

        return $used
            ? "\"{$model->name}\" is used by {$used} ".str('entry')->plural($used).'. Untick "Active" to hide it from the form instead.'
            : null;
    }
}
