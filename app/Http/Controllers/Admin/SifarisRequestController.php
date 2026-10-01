<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SifarisApplicationRequest;
use App\Models\SifarisRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin side of sifaris: pending / approved / disapproved lists, review and the letter details. */
class SifarisRequestController extends Controller
{
    /** Tab metadata shared by the three list pages. */
    public const LISTS = [
        SifarisRequest::PENDING => ['title' => 'Pending Sifaris', 'icon' => '⏳', 'empty' => 'No sifaris requests are waiting for review.'],
        SifarisRequest::APPROVED => ['title' => 'Approved Sifaris', 'icon' => '✅', 'empty' => 'No approved sifaris yet.'],
        SifarisRequest::REJECTED => ['title' => 'Disapproved Sifaris', 'icon' => '⛔', 'empty' => 'No disapproved sifaris requests.'],
    ];

    public function pending(): View
    {
        return $this->list(SifarisRequest::PENDING);
    }

    public function approved(): View
    {
        return $this->list(SifarisRequest::APPROVED);
    }

    public function rejected(): View
    {
        return $this->list(SifarisRequest::REJECTED);
    }

    private function list(string $status): View
    {
        return view('dashboard.sifaris.list', [
            'status' => $status,
            'meta' => self::LISTS[$status],
            'counts' => SifarisRequest::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'rows' => SifarisRequest::query()->where('status', $status)->latest('id')->get(),
        ]);
    }

    public function show(SifarisRequest $sifaris): View
    {
        return view('dashboard.sifaris.show', ['sifaris' => $sifaris->load(['user', 'reviewer'])]);
    }

    public function edit(SifarisRequest $sifaris): View
    {
        return view('dashboard.sifaris.form', [
            'sifaris' => $sifaris,
            'values' => $sifaris->only([
                'applicant_name', 'parent_relation', 'parent_name', 'grandparent_relation', 'grandparent_name',
                'province', 'district', 'municipality', 'ward_no', 'mobile', 'email',
            ]),
        ]);
    }

    public function update(SifarisApplicationRequest $request, SifarisRequest $sifaris): RedirectResponse
    {
        $sifaris->update($request->sifarisData());

        return redirect()->route('dashboard.sifaris.show', $sifaris)->with('dashboard-status', 'Sifaris request updated.');
    }

    /** Approve, saving the letter number, dispatch number and date printed on the letter. */
    public function approve(Request $request, SifarisRequest $sifaris): RedirectResponse
    {
        $sifaris->approve($request->user(), $this->letterDetails($request));

        return redirect()->route('dashboard.sifaris.show', $sifaris)
            ->with('dashboard-status', $sifaris->applicant_name.'\'s sifaris approved. The applicant can now download the letter.');
    }

    /** Change the letter number, dispatch number or date without changing the status. */
    public function updateLetter(Request $request, SifarisRequest $sifaris): RedirectResponse
    {
        $sifaris->update($this->letterDetails($request));

        return back()->with('dashboard-status', 'Letter details saved.');
    }

    public function reject(Request $request, SifarisRequest $sifaris): RedirectResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $sifaris->reject($request->user(), $data['reason'] ?? null);

        return back()->with('dashboard-status', $sifaris->applicant_name.'\'s sifaris request was disapproved.');
    }

    public function destroy(SifarisRequest $sifaris): RedirectResponse
    {
        $status = $sifaris->status;

        $sifaris->deletePhoto();
        $sifaris->delete();

        return redirect()->route('dashboard.sifaris.'.$status)->with('dashboard-status', 'Sifaris request deleted.');
    }

    private function letterDetails(Request $request): array
    {
        return $request->validate([
            'letter_number' => ['nullable', 'string', 'max:50'],
            'dispatch_number' => ['nullable', 'string', 'max:50'],
            'letter_date' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
