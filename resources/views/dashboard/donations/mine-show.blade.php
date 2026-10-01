<x-layouts.dashboard title="View Donation">
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-navy">💰 Donation Details</h2>
            <a href="{{ route('dashboard.my-donations.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to My Donations</a>
        </div>

        @if (session('dashboard-error'))
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
        @endif

        @if ($donation->isReturned())
        <div class="mb-4 rounded-lg border-l-4 border-orange-400 bg-orange-50 p-4 sm:flex sm:items-center sm:gap-4">
            <div class="min-w-0 flex-1">
                <h3 class="font-bold text-orange-900">Returned for correction</h3>
                <p class="mt-1 text-sm text-orange-800">{{ $donation->review_note ?: 'Please check the details and submit again.' }}</p>
            </div>
            <a href="{{ route('dashboard.my-donations.edit', $donation->id) }}" class="btn-maroon mt-3 w-full justify-center !px-5 !py-2.5 text-sm sm:mt-0 sm:w-auto">Edit &amp; Resubmit</a>
        </div>
        @elseif ($donation->isApproved())
        <div class="mb-4 rounded-lg border-l-4 border-green-400 bg-green-50 p-4">
            <h3 class="font-bold text-green-800">✅ Donation approved</h3>
            <p class="mt-1 text-sm text-green-700">Approved on {{ $donation->reviewed_at?->format('d M, Y') }}. Thank you for your donation.</p>
        </div>
        @else
        <div class="mb-4 rounded-lg border-l-4 border-amber-300 bg-amber-50 p-4">
            <h3 class="font-bold text-amber-900">⏳ Waiting for admin review</h3>
            <p class="mt-1 text-sm text-amber-800">An admin will check your donation and bank voucher. You can edit it only if it is returned for correction.</p>
        </div>
        @endif

        <div class="card grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="flex items-start gap-4">
                <img src="{{ $donation->displayImage() }}" alt="" class="h-20 w-20 shrink-0 rounded-full object-cover">
                <div class="min-w-0">
                    <div class="text-lg font-extrabold text-navy">{{ $donation->donor_name }}</div>
                    <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-semibold {{ $donation->statusClasses() }}">{{ $donation->statusLabel() }}</span>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-xs font-semibold uppercase text-gray-400">Amount</dt><dd class="text-lg font-bold text-gray-800">Rs. {{ number_format((float) $donation->amount, 2) }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-gray-400">Donation Date</dt><dd>{{ $donation->donate_date->format('d M, Y') }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-gray-400">Address</dt><dd>{{ $donation->address ?: '—' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-gray-400">Submitted</dt><dd>{{ $donation->created_at->format('d M, Y h:i A') }}</dd></div>
                    </dl>
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase text-gray-400">Paid Bank Voucher</div>
                @if ($donation->voucher_url)
                <a href="{{ $donation->voucher_url }}" target="_blank" rel="noopener" class="mt-2 block" title="Open full size">
                    <img src="{{ $donation->voucher_url }}" alt="Bank voucher" class="max-h-80 w-full rounded-md border border-gray-200 bg-gray-50 object-contain">
                </a>
                <p class="mt-1 text-xs text-gray-400">Click the image to open it full size.</p>
                @else
                <p class="mt-2 text-sm text-gray-400">No voucher attached.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.dashboard>
