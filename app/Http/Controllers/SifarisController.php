<?php

namespace App\Http\Controllers;

use App\Http\Requests\SifarisApplicationRequest;
use App\Models\SifarisRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Sifaris (recommendation letter) pages for signed-in users: apply, follow the request, download the letter. */
class SifarisController extends Controller
{
    /** Public page explaining the sifaris (the Apply button sends guests to login). */
    public function info(): View
    {
        return view('sifaris.index');
    }

    public function index(Request $request): View
    {
        return view('dashboard.sifaris.mine', [
            'requests' => $request->user()->sifarisRequests()->latest('id')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.sifaris.form', [
            'sifaris' => null,
            'values' => ['email' => $user->email],
        ]);
    }

    public function store(SifarisApplicationRequest $request): RedirectResponse
    {
        $request->user()->sifarisRequests()->create($request->sifarisData() + [
            'status' => SifarisRequest::PENDING,
            'applied_at' => today(),
        ]);

        return redirect()->route('dashboard.my-sifaris.index')
            ->with('dashboard-status', 'Your sifaris request has been submitted. You can download the letter here once it is approved.');
    }

    /** The letter with the applicant's details merged in. Owners see it once approved; admins see every request. */
    public function letter(Request $request, SifarisRequest $sifaris): View
    {
        $isAdmin = $request->user()->can('sifaris.view');
        abort_unless($sifaris->user_id === $request->user()->id || $isAdmin, 403);
        abort_unless($sifaris->isApproved() || $isAdmin, 404);

        return view('dashboard.sifaris.letter', ['sifaris' => $sifaris]);
    }
}
