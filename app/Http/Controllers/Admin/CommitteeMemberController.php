<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/** Committee members: plain dashboard CRUD with the committee and sub type loaded for the list. */
class CommitteeMemberController extends ResourceController
{
    protected function query(): Builder
    {
        return parent::query()->with(['committeeType', 'committeeSubType']);
    }

    /** A sub type must belong to the chosen committee. */
    protected function rules(?Model $model): array
    {
        $rules = parent::rules($model);

        $rules['committee_sub_type_id'][] = Rule::exists('committee_sub_types', 'id')
            ->where('committee_type_id', (int) request()->input('committee_type_id'));

        return $rules;
    }
}
