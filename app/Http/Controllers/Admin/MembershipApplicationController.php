<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipApplicationRequest;
use App\Models\Membership;
use App\Models\MembershipType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin side of membership: separate lists for pending, approved and disapproved applications. */
class MembershipApplicationController extends Controller
{
    /** Tab metadata shared by the three list pages. */
    public const LISTS = [
        Membership::PENDING => ['title' => 'Pending Applications', 'icon' => '⏳', 'empty' => 'No applications are waiting for review.'],
        Membership::APPROVED => ['title' => 'Approved Members', 'icon' => '✅', 'empty' => 'No approved members yet.'],
        Membership::REJECTED => ['title' => 'Disapproved Applications', 'icon' => '⛔', 'empty' => 'No disapproved applications.'],
    ];

    public function pending(): View
    {
        return $this->list(Membership::PENDING);
    }

    public function approved(): View
    {
        return $this->list(Membership::APPROVED);
    }

    public function rejected(): View
    {
        return $this->list(Membership::REJECTED);
    }

    private function list(string $status): View
    {
        return view('dashboard.membership.list', [
            'status' => $status,
            'meta' => self::LISTS[$status],
            'lists' => self::LISTS,
            'counts' => Membership::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'rows' => Membership::query()->with('type')->where('status', $status)->latest('id')->get(),
        ]);
    }

    public function show(Membership $membership): View
    {
        return view('dashboard.membership.show', ['membership' => $membership->load(['type', 'user', 'reviewer'])]);
    }

    public function edit(Membership $membership): View
    {
        return view('dashboard.membership.apply', [
            'types' => MembershipType::query()->orderBy('sort_order')->orderBy('id')->get(),
            'membership' => $membership,
            'values' => $membership->only([
                'membership_type_id', 'full_name', 'surname', 'permanent_address', 'current_address',
                'province', 'district', 'municipality', 'ward_no', 'mobile', 'email', 'occupation',
            ]) + [
                'date_of_birth' => $membership->date_of_birth?->format('Y-m-d'),
                'expires_at' => $membership->expires_at?->format('Y-m-d'),
            ],
            'provinces' => config('nepal.provinces'),
        ]);
    }

    public function update(MembershipApplicationRequest $request, Membership $membership): RedirectResponse
    {
        $data = $request->applicationData($membership);

        if ($membership->isApproved()) {
            $data['expires_at'] = $request->filled('expires_at') ? $request->date('expires_at')->endOfDay() : null;
        }

        $membership->update($data);

        return redirect()->route('dashboard.membership.show', $membership)->with('dashboard-status', 'Application updated.');
    }

    public function approve(Request $request, Membership $membership): RedirectResponse
    {
        if ($membership->isApproved()) {
            return back()->with('dashboard-error', 'This application is already approved.');
        }

        $membership->load('type')->approve($request->user());

        return back()->with('dashboard-status', $membership->displayName().' approved. Membership number '.$membership->membership_number.'.');
    }

    public function reject(Request $request, Membership $membership): RedirectResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $membership->reject($request->user(), $data['reason'] ?? null);

        return back()->with('dashboard-status', $membership->displayName().' was disapproved.');
    }

    public function destroy(Membership $membership): RedirectResponse
    {
        $status = $membership->status;

        $membership->deleteFiles();
        $membership->delete();

        return redirect()->route('dashboard.membership.'.$status)->with('dashboard-status', 'Application deleted.');
    }
}
