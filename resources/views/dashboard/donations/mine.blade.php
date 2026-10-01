<x-layouts.dashboard title="My Donations">
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-navy">💰 My Donations</h2>
                <p class="text-sm text-gray-500">Donations you added. An admin checks each one before it is approved.</p>
            </div>
            <a href="{{ route('dashboard.my-donations.create') }}" class="btn-maroon justify-center">+ Add Donation</a>
        </div>

        @if (session('dashboard-status'))
        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
        @endif
        @if (session('dashboard-error'))
        <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
        @endif

        @if ($donations->isEmpty())
        <div class="card mt-5 text-center">
            <div class="text-5xl">💰</div>
            <h3 class="mt-3 text-lg font-bold text-navy">No donations yet</h3>
            <p class="mx-auto mt-1 max-w-md text-sm text-gray-600">Add your donation here. Once an admin approves it, it will be shown as approved.</p>
            <a href="{{ route('dashboard.my-donations.create') }}" class="btn-maroon mt-5 justify-center">Add Donation</a>
        </div>
        @else
        <div class="mt-5 flex flex-col gap-1 rounded-lg bg-navy p-5 text-white shadow-md sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm font-semibold uppercase tracking-wider">My Approved Donations</div>
            <div class="text-2xl font-extrabold sm:text-3xl">Rs. {{ number_format((float) $approvedTotal, 2) }}</div>
        </div>

        <div class="mt-5 space-y-3">
            @foreach ($donations as $donation)
            <div class="card flex flex-col gap-4 sm:flex-row sm:items-center">
                <img src="{{ $donation->displayImage() }}" alt="" class="h-14 w-14 shrink-0 rounded-full object-cover">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-navy">{{ $donation->donor_name }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $donation->statusClasses() }}">{{ $donation->statusLabel() }}</span>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">
                        <span class="font-semibold text-gray-800">Rs. {{ number_format((float) $donation->amount, 2) }}</span>
                        · {{ $donation->donate_date->format('d M, Y') }}
                        @if ($donation->address) · {{ $donation->address }} @endif
                    </div>
                    @if ($donation->isReturned())
                    <p class="mt-2 rounded-md bg-orange-50 px-3 py-2 text-sm text-orange-800">
                        <strong>Returned for correction:</strong> {{ $donation->review_note ?: 'Please check the details and submit again.' }}
                    </p>
                    @elseif ($donation->isApproved())
                    <p class="mt-1 text-xs text-green-700">Approved on {{ $donation->reviewed_at?->format('d M, Y') }}</p>
                    @else
                    <p class="mt-1 text-xs text-gray-400">Waiting for admin review</p>
                    @endif
                </div>
                <div class="flex shrink-0 gap-2">
                    <a href="{{ route('dashboard.my-donations.show', $donation->id) }}" class="flex-1 rounded-md border border-navy px-4 py-2 text-center text-sm font-semibold text-navy hover:bg-navy-50">View</a>
                    @if ($donation->isReturned())
                    <a href="{{ route('dashboard.my-donations.edit', $donation->id) }}" class="flex-1 rounded-md bg-maroon px-4 py-2 text-center text-sm font-semibold text-white hover:bg-maroon-700">Edit &amp; Resubmit</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-layouts.dashboard>
