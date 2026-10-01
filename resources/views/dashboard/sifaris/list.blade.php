@php
    $isApproved = $status === 'approved';
    $isRejected = $status === 'rejected';
    $tabLabels = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Disapproved'];
@endphp
<x-layouts.dashboard :title="$meta['title']">
    <div>
        <h2 class="text-xl font-bold text-navy">{{ $meta['icon'] }} {{ $meta['title'] }}</h2>
        <p class="text-sm text-gray-500">{{ number_format($rows->count()) }} {{ Str::plural('request', $rows->count()) }}</p>
    </div>

    {{-- Pending / Approved / Disapproved tabs --}}
    <div class="mt-4 flex gap-1 overflow-x-auto rounded-lg bg-white p-1 shadow-sm">
        @foreach ($tabLabels as $tabStatus => $tabLabel)
        <a href="{{ route('dashboard.sifaris.'.$tabStatus) }}" class="flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 py-2 text-sm font-semibold transition {{ $status === $tabStatus ? 'bg-navy text-white shadow' : 'text-gray-600 hover:bg-gray-100' }}">
            {{ $tabLabel }}
            <span class="rounded-full px-2 text-xs {{ $status === $tabStatus ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts[$tabStatus] ?? 0 }}</span>
        </a>
        @endforeach
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif

    <div class="mt-4 rounded-lg bg-white p-3 shadow-md sm:p-4">
        <table id="resource-table" class="display w-full text-sm" style="width:100%">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Applicant</th>
                    <th>Ref.</th>
                    <th>Address</th>
                    <th>Mobile</th>
                    <th>Applied</th>
                    @if ($isApproved)
                    <th>Letter No.</th>
                    @endif
                    @if ($isRejected)
                    <th>Reason</th>
                    @endif
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                <tr>
                    <td><img src="{{ $row->photo_url }}" alt="" loading="lazy" class="h-10 w-10 rounded-full object-cover"></td>
                    <td class="np font-semibold text-navy">{{ $row->applicant_name }}</td>
                    <td class="whitespace-nowrap font-mono text-xs">{{ $row->reference() }}</td>
                    <td class="np">{{ $row->municipality }}-{{ $row->ward_no }}, {{ $row->district }}</td>
                    <td class="whitespace-nowrap">{{ $row->mobile }}</td>
                    <td data-order="{{ $row->applied_at->format('Y-m-d') }}" class="whitespace-nowrap">{{ $row->applied_at->format('d M, Y') }}</td>
                    @if ($isApproved)
                    <td class="np">{{ $row->letter_number ?: '—' }}</td>
                    @endif
                    @if ($isRejected)
                    <td>{{ $row->rejection_reason ? Str::limit($row->rejection_reason, 50) : '—' }}</td>
                    @endif
                    <td class="whitespace-nowrap text-right">
                        <a href="{{ route('dashboard.sifaris.show', $row) }}" class="mr-2 text-xs font-semibold text-navy hover:underline">{{ $status === 'pending' ? 'Review' : 'View' }}</a>
                        @if ($isApproved)
                        <a href="{{ route('dashboard.sifaris.letter', $row) }}" class="mr-2 text-xs font-semibold text-maroon hover:underline">Letter</a>
                        @endif
                        @can('sifaris.delete')
                        <form method="POST" action="{{ route('dashboard.sifaris.destroy', $row) }}" class="inline" onsubmit="return confirm('Delete this sifaris request permanently? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script>
        new DataTable('#resource-table', {
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: -1 },
                { orderable: false, targets: [0, -1] },
            ],
            language: { search: '', searchPlaceholder: 'Search…', emptyTable: @json($meta['empty']) },
        });
    </script>
    @endpush
</x-layouts.dashboard>
