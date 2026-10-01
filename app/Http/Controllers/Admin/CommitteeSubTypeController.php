<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Committee sub types (e.g. districts of the District Committee); cannot be deleted while members use them. */
class CommitteeSubTypeController extends ResourceController
{
    protected function query(): Builder
    {
        return parent::query()->with('committeeType')->withCount('members');
    }

    protected function deleteBlockedReason(Model $model): ?string
    {
        return $model->members_count > 0
            ? 'This sub type still has members. Move or delete them first.'
            : null;
    }
}
