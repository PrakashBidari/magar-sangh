{{-- Where to pay before applying: QR and bank details from Lakhan Thapa Pratisthan > Settings --}}
@if ($siteSettings->donation_qr_url || $siteSettings->donation_bank_details)
<div class="card mb-5">
    <h3 class="font-bold text-navy">🏦 How to donate</h3>
    <p class="text-sm text-gray-500">Pay by scanning the QR code or depositing to the bank account below, then fill in this form and attach the paid voucher.</p>

    <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-start">
        @if ($siteSettings->donation_qr_url)
        <a href="{{ $siteSettings->donation_qr_url }}" target="_blank" rel="noopener" class="shrink-0 self-center sm:self-start" title="Open full size">
            <img src="{{ $siteSettings->donation_qr_url }}" alt="Donation QR code" class="h-48 w-48 rounded-md border border-gray-200 bg-white object-contain p-1">
            <span class="mt-1 block text-center text-xs font-semibold text-navy">Scan to pay</span>
        </a>
        @endif
        @if ($siteSettings->donation_bank_details)
        <div class="min-w-0 flex-1">
            <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Bank details</div>
            <div class="np mt-1 break-words rounded-md bg-gray-50 px-4 py-3 text-sm leading-relaxed text-gray-800">{!! nl2br(e($siteSettings->donation_bank_details)) !!}</div>
        </div>
        @endif
    </div>
</div>
@endif
