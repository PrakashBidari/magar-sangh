<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** Donations added here by staff who may approve them go straight onto the site. */
class DonationController extends ResourceController
{
    protected function query(): Builder
    {
        return parent::query()->with('user');
    }

    protected function persist(Request $request, ?Model $model): Model
    {
        $donation = parent::persist($request, $model);

        if (! $model && $request->user()->can('donations.approve')) {
            $donation->approve($request->user());
        }

        return $donation;
    }
}
