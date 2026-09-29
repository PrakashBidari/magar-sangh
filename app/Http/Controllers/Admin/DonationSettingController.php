<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationDocument;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Lakhan Thapa Pratisthan page settings: rich content plus titled PDF files, both shown above the donation list. */
class DonationSettingController extends Controller
{
    public function edit(): View
    {
        return view('dashboard.donations.settings', [
            'settings' => Setting::current(),
            'documents' => DonationDocument::latest('id')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['donation_page_content' => ['nullable', 'string']]);

        Setting::current()->update(['donation_page_content' => $data['donation_page_content'] ?? null]);

        return redirect()->route('dashboard.donation-settings')
            ->with('dashboard-status', 'Lakhan Thapa Pratisthan page updated.');
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        DonationDocument::create([
            'title' => $data['title'],
            'file_url' => '/storage/'.$request->file('pdf')->store('donation-documents', 'public'),
        ]);

        return redirect()->route('dashboard.donation-settings')->with('dashboard-status', 'PDF added.');
    }

    public function destroyDocument(DonationDocument $document): RedirectResponse
    {
        if (Str::startsWith($document->file_url, '/storage/')) {
            Storage::disk('public')->delete(Str::after($document->file_url, '/storage/'));
        }
        $document->delete();

        return redirect()->route('dashboard.donation-settings')->with('dashboard-status', 'PDF deleted.');
    }
}
