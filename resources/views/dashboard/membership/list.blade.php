@php
    $isApproved = $status === 'approved';
    $isRejected = $status === 'rejected';
    $tabRoutes = ['pending' => 'dashboard.membership.pending', 'approved' => 'dashboard.membership.approved', 'rejected' => 'dashboard.membership.rejected'];
    $tabLabels = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Disapproved'];
@endphp
<x-layouts.dashboard :title="$meta['title']">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-navy">{{ $meta['icon'] }} {{ $meta['title'] }}</h2>
            <p class="text-sm text-gray-500">{{ number_format($rows->count()) }} {{ Str::plural('record', $rows->count()) }}</p>
        </div>
        <a href="{{ route('dashboard.membership-types.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-navy hover:bg-gray-50">🏷️ Membership Types</a>
    </div>

    {{-- Pending / Approved / Disapproved tabs --}}
    <div class="mt-4 flex gap-1 overflow-x-auto rounded-lg bg-white p-1 shadow-sm">
        @foreach ($tabRoutes as $tabStatus => $tabRoute)
        <a href="{{ route($tabRoute) }}" class="flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 py-2 text-sm font-semibold transition {{ $status === $tabStatus ? 'bg-navy text-white shadow' : 'text-gray-600 hover:bg-gray-100' }}">
            {{ $tabLabels[$tabStatus] }}
            <span class="rounded-full px-2 text-xs {{ $status === $tabStatus ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts[$tabStatus] ?? 0 }}</span>
        </a>
        @endforeach
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif
    @if (session('dashboard-error'))
    <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
    @endif

    <div class="mt-4 rounded-lg bg-white p-3 shadow-md sm:p-4">
        <table id="resource-table" class="display w-full text-sm" style="width:100%">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Member No.</th>
                    <th>Type</th>
                    <th>Mobile</th>
                    <th>Applied</th>
                    @if ($isApproved)
                    <th>Valid Till</th>
                    <th>Status</th>
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
                    <td class="font-semibold text-navy">{{ $row->displayName() }}</td>
                    <td class="whitespace-nowrap font-mono text-xs">{{ $row->membership_number ?: '—' }}</td>
                    <td>{{ $row->type->name_en }}</td>
                    <td class="whitespace-nowrap">{{ $row->mobile }}</td>
                    <td data-order="{{ $row->applied_at->format('Y-m-d') }}" class="whitespace-nowrap">{{ $row->applied_at->format('d M, Y') }}</td>
                    @if ($isApproved)
                    <td data-order="{{ $row->expires_at?->format('Y-m-d') ?? '9999-12-31' }}" class="whitespace-nowrap">{{ $row->expires_at?->format('d M, Y') ?? 'Lifetime' }}</td>
                    <td><span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $row->statusClasses() }}">{{ $row->statusLabel() }}</span></td>
                    @endif
                    @if ($isRejected)
                    <td>{{ $row->rejection_reason ? Str::limit($row->rejection_reason, 50) : '—' }}</td>
                    @endif
                    <td class="whitespace-nowrap text-right">
                        <a href="{{ route('dashboard.membership.show', $row) }}" class="mr-2 text-xs font-semibold text-navy hover:underline">View</a>
                        <a href="{{ route('dashboard.membership.edit', $row) }}" class="mr-2 text-xs font-semibold text-navy hover:underline">Edit</a>
                        @if ($isApproved)
                        <a href="{{ route('dashboard.membership.card', $row) }}" class="mr-2 text-xs font-semibold text-maroon hover:underline">ID Card</a>
                        @endif
                        @unless ($isApproved)
                        <form method="POST" action="{{ route('dashboard.membership.approve', $row) }}" class="inline">
                            @csrf
                            <button type="submit" class="mr-2 text-xs font-semibold text-green-700 hover:underline">Approve</button>
                        </form>
                        @endunless
                        @unless ($isRejected)
                        <button type="button" data-reject="{{ route('dashboard.membership.reject', $row) }}" data-name="{{ $row->displayName() }}" class="mr-2 text-xs font-semibold text-amber-700 hover:underline">Disapprove</button>
                        @endunless
                        <form method="POST" action="{{ route('dashboard.membership.destroy', $row) }}" class="inline" onsubmit="return confirm('Delete this application permanently? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('dashboard.membership._reject-dialog')

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
