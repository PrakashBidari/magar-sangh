<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Committee types are plain dashboard CRUD, but cannot be deleted while members use them. */
class CommitteeTypeController extends ResourceController
{
    protected function query(): Builder
    {
        return parent::query()->withCount('members');
    }

    protected function deleteBlockedReason(Model $model): ?string
    {
        return $model->members_count > 0
            ? 'This committee still has members. Move or delete them first.'
            : null;
    }
}
