<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SifarisSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Sifaris settings: the signature, signatory and contact details printed on every letter. Edit only. */
class SifarisSettingController extends Controller
{
    public function edit(): View
    {
        return view('dashboard.sifaris.settings', ['settings' => SifarisSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'signatory_name' => ['required', 'string', 'max:100'],
            'signatory_title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'string', 'max:150'],
            'signature' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_signature' => ['nullable', 'boolean'],
        ]);

        $settings = SifarisSetting::current();
        $settings->fill(Arr::only($data, ['signatory_name', 'signatory_title', 'phone', 'email', 'website']));

        if ($request->hasFile('signature') || $request->boolean('remove_signature')) {
            $this->deleteStoredFile($settings->signature_url);
            $settings->signature_url = $request->hasFile('signature')
                ? '/storage/'.$request->file('signature')->store('signatures', 'public')
                : null;
        }

        $settings->save();

        return redirect()->route('dashboard.sifaris.settings')
            ->with('dashboard-status', 'Sifaris settings saved.');
    }

    /** Uploaded files only; the default signature in public/images is left alone. */
    protected function deleteStoredFile(?string $url): void
    {
        if ($url && Str::startsWith($url, '/storage/')) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }
    }
}
