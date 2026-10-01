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

/**
 * Lakhan Thapa Pratisthan settings: rich content plus titled PDF files (both shown above the donation list),
 * and the QR / bank details shown to members on the "Apply For Donation" page.
 */
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
        $data = $request->validate([
            'donation_page_content' => ['nullable', 'string'],
            'donation_bank_details' => ['nullable', 'string', 'max:2000'],
            'donation_qr' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'remove_donation_qr' => ['nullable', 'boolean'],
        ]);

        $settings = Setting::current();
        $settings->fill([
            'donation_page_content' => $data['donation_page_content'] ?? null,
            'donation_bank_details' => $data['donation_bank_details'] ?? null,
        ]);

        if ($request->hasFile('donation_qr') || $request->boolean('remove_donation_qr')) {
            $this->deleteStoredFile($settings->donation_qr_url);
            $settings->donation_qr_url = $request->hasFile('donation_qr')
                ? '/storage/'.$request->file('donation_qr')->store('donation-qr', 'public')
                : null;
        }

        $settings->save();

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
        $this->deleteStoredFile($document->file_url);
        $document->delete();

        return redirect()->route('dashboard.donation-settings')->with('dashboard-status', 'PDF deleted.');
    }

    protected function deleteStoredFile(?string $url): void
    {
        if ($url && Str::startsWith($url, '/storage/')) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }
    }
}
