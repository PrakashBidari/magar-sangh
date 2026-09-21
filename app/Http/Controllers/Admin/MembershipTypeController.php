<?php

namespace App\Http\Controllers\Admin;

use App\Models\MembershipType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** Membership types are plain dashboard CRUD, plus lifetime handling and delete protection. */
class MembershipTypeController extends ResourceController
{
    protected function query(): Builder
    {
        return parent::query()->withCount('memberships');
    }

    protected function payload(Request $request, ?Model $model): array
    {
        $data = parent::payload($request, $model);

        if (($data['duration_unit'] ?? null) === MembershipType::LIFETIME) {
            $data['duration_value'] = null;
        }

        return $data;
    }

    protected function deleteBlockedReason(Model $model): ?string
    {
        return $model->memberships_count > 0
            ? 'This type already has applications. Close it to new applications instead of deleting it.'
            : null;
    }
}
