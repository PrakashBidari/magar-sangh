<x-layouts.dashboard title="My Sifaris">
    <div class="mx-auto max-w-4xl">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-navy">📜 My Sifaris</h2>
            @if ($requests->isNotEmpty())
            <a href="{{ route('dashboard.my-sifaris.create') }}" class="btn-maroon justify-center !px-5 !py-2.5 text-sm">➕ New sifaris request</a>
            @endif
        </div>

        @if (session('dashboard-status'))
        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
        @endif

        @if ($requests->isEmpty())
        <div class="card mt-4 text-center">
            <div class="text-5xl">📜</div>
            <h3 class="mt-3 text-lg font-bold text-navy">Request a sifaris (सिफारिस)</h3>
            <p class="mx-auto mt-1 max-w-md text-sm text-gray-600">Get a recommendation letter from the Nepal Magar Association confirming that you are Magar. Once the office approves it, you can download the letter here as a PDF or image.</p>
            <a href="{{ route('dashboard.my-sifaris.create') }}" class="btn-maroon mt-5 justify-center">Apply for sifaris</a>
        </div>
        @else
        <div class="mt-4 space-y-4">
            @foreach ($requests as $item)
            <div class="card flex flex-col gap-4 sm:flex-row sm:items-center">
                <img src="{{ $item->photo_url }}" alt="" class="h-24 w-20 shrink-0 self-start rounded-md border border-gray-200 object-cover sm:self-center">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="np text-lg font-extrabold text-navy">{{ $item->applicant_name }}</h3>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $item->statusClasses() }}">{{ $item->statusLabel() }}</span>
                    </div>
                    <p class="np text-sm text-gray-500">{{ $item->parent_name }} को {{ $item->parent_relation }} · {{ $item->municipality }}-{{ $item->ward_no }}, {{ $item->district }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $item->reference() }} · Applied {{ $item->applied_at->format('d M, Y') }}@if ($item->isApproved()) · Approved {{ $item->approved_at->format('d M, Y') }}@endif</p>
                    @if ($item->isPending())
                    <p class="mt-2 text-sm text-amber-700">⏳ Under review. The download will appear here once it is approved.</p>
                    @elseif ($item->isRejected())
                    <p class="mt-2 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">⛔ {{ $item->rejection_reason ?: 'Not approved. Please contact the association for details, or apply again with corrected information.' }}</p>
                    @endif
                </div>
                @if ($item->isApproved())
                <a href="{{ route('dashboard.sifaris.letter', $item) }}" class="btn-maroon shrink-0 justify-center !px-5 !py-2.5 text-sm">⬇ View &amp; download</a>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-layouts.dashboard>
