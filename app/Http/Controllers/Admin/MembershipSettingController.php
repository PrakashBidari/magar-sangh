<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Membership settings: the authorized signature printed on every ID card. */
class MembershipSettingController extends Controller
{
    public function edit(): View
    {
        return view('dashboard.membership.settings', ['settings' => Setting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'authorized_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signature' => ['nullable', 'boolean'],
        ]);

        $settings = Setting::current();

        if ($request->hasFile('authorized_signature') || $request->boolean('remove_signature')) {
            $this->deleteStoredFile($settings->authorized_signature_url);
            $settings->authorized_signature_url = $request->hasFile('authorized_signature')
                ? '/storage/'.$request->file('authorized_signature')->store('signatures', 'public')
                : null;
            $settings->save();
        }

        return redirect()->route('dashboard.membership.settings')
            ->with('dashboard-status', 'Membership settings saved.');
    }

    protected function deleteStoredFile(?string $url): void
    {
        if ($url && Str::startsWith($url, '/storage/')) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }
    }
}
