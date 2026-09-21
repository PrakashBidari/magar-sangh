<?php

namespace App\Http\Controllers;

use App\Http\Requests\MembershipApplicationRequest;
use App\Models\Membership;
use App\Models\MembershipType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Membership pages for signed-in users: apply, follow the application, and get the ID card. */
class MembershipController extends Controller
{
    /** Public page listing the membership types (the Apply button sends guests to login). */
    public function types(): View
    {
        return view('membership.types', ['types' => MembershipType::active()->get()]);
    }

    public function show(Request $request): View
    {
        $memberships = $request->user()->memberships()->with('type')->latest('id')->get();

        return view('dashboard.membership.mine', [
            'memberships' => $memberships,
            'current' => $memberships->first(),
            'canApply' => $this->canApply($request),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->canApply($request)) {
            return redirect()->route('dashboard.my-membership.show')
                ->with('dashboard-error', 'You already have a pending application or an active membership.');
        }

        $user = $request->user();
        $types = MembershipType::active()->get();

        return view('dashboard.membership.apply', [
            'types' => $types,
            'membership' => null,
            'values' => [
                'membership_type_id' => $request->integer('type') ?: null,
                'full_name' => $user->name,
                'email' => $user->email,
            ],
            'provinces' => config('nepal.provinces'),
        ]);
    }

    public function store(MembershipApplicationRequest $request): RedirectResponse
    {
        if (! $this->canApply($request)) {
            return redirect()->route('dashboard.my-membership.show')
                ->with('dashboard-error', 'You already have a pending application or an active membership.');
        }

        $request->user()->memberships()->create($request->applicationData() + [
            'status' => Membership::PENDING,
            'applied_at' => today(),
        ]);

        return redirect()->route('dashboard.my-membership.show')
            ->with('dashboard-status', 'Your application has been submitted. We will review it and get back to you soon.');
    }

    /** The printable ID card; only for approved memberships, owner or admin. */
    public function card(Request $request, Membership $membership): View
    {
        $this->authorizeAccess($request, $membership);
        abort_unless($membership->isApproved(), 404);

        return view('dashboard.membership.card', ['membership' => $membership->load('type')]);
    }

    /** Payment vouchers live on the private disk, so they are streamed to the owner or an admin only. */
    public function voucher(Request $request, Membership $membership): StreamedResponse
    {
        $this->authorizeAccess($request, $membership);
        abort_unless($membership->voucher_path && Storage::disk('local')->exists($membership->voucher_path), 404);

        return Storage::disk('local')->response($membership->voucher_path);
    }

    private function authorizeAccess(Request $request, Membership $membership): void
    {
        abort_unless($membership->user_id === $request->user()->id || $request->user()->hasRole('admin'), 403);
    }

    private function canApply(Request $request): bool
    {
        $user = $request->user();

        return ! $user->memberships()->where('status', Membership::PENDING)->exists()
            && ! $user->memberships()->active()->exists();
    }
}
