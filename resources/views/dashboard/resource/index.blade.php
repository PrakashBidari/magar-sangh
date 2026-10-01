@php
    $readonly = $cfg['readonly'] ?? false;
    $canEdit = auth()->user()->can($key.'.edit');
    $canDelete = auth()->user()->can($key.'.delete');
    $canApprove = ($cfg['approvable'] ?? false) && auth()->user()->can($key.'.approve');
    $rejectLabel = $cfg['reject_label'] ?? 'Disapprove';
    $rejectReason = $cfg['reject_reason'] ?? false;
    $columns = $cfg['columns'];
    $firstTextColumn = collect($columns)->search(fn ($c) => ($c['type'] ?? 'text') === 'text');
    $nonOrderable = collect($columns)->keys()->filter(fn ($i) => in_array($columns[$i]['type'] ?? 'text', ['image', 'file']))->values();
@endphp
<x-layouts.dashboard :title="$cfg['label']">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-navy">{{ $cfg['icon'] }} {{ $cfg['label'] }}</h2>
            <p class="text-sm text-gray-500">{{ number_format($rows->count()) }} {{ Str::plural('record', $rows->count()) }}</p>
        </div>
        @if (! $readonly && auth()->user()->can($key.'.create'))
        <a href="{{ route('dashboard.'.$key.'.create') }}" class="btn-maroon justify-center">+ Add {{ $cfg['singular'] }}</a>
        @endif
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif
    @if (session('dashboard-error'))
    <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
    @endif

    {{-- Optional money total above the table (e.g. donations), set with 'summary' in config/admin.php --}}
    @isset ($cfg['summary'])
    <div class="mt-5 flex flex-col gap-1 rounded-lg bg-navy p-5 text-white shadow-md sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm font-semibold uppercase tracking-wider">{{ $cfg['summary']['label'] }}</div>
        <div class="text-2xl font-extrabold sm:text-3xl">Rs. {{ number_format((float) $cfg['model']::query()->when($cfg['summary']['scope'] ?? null, fn ($q, $scope) => $q->{$scope}())->sum($cfg['summary']['sum']), 2) }}</div>
    </div>
    @endisset

    <div class="mt-5 rounded-lg bg-white p-3 shadow-md sm:p-4">
        <table id="resource-table" class="display w-full text-sm" style="width:100%">
            <thead>
                <tr>
                    @foreach ($columns as $col)
                    <th>{{ $col['label'] }}</th>
                    @endforeach
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                <tr>
                    @foreach ($columns as $col)
                        @php
                            $type = $col['type'] ?? 'text';
                            $value = $col['field'] === 'roles' ? null : data_get($row, $col['field']);
                        @endphp
                        @switch($type)
                            @case('image')
                                <td>
                                    @if ($value && ($col['link'] ?? false))
                                    <a href="{{ $value }}" target="_blank" rel="noopener" title="Open full size">
                                        <img src="{{ $value }}" alt="" loading="lazy" class="h-10 w-10 rounded border border-gray-200 object-cover transition hover:ring-2 hover:ring-navy">
                                    </a>
                                    @elseif ($value)
                                    <img src="{{ $value }}" alt="" loading="lazy" class="h-10 w-10 rounded {{ ($col['fit'] ?? 'cover') === 'contain' ? 'bg-white object-contain' : 'object-cover' }}">
                                    @else
                                    <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                @break
                            @case('date')
                                <td data-order="{{ $value?->format('Y-m-d') }}">{{ $value?->format('d M, Y') ?? '—' }}</td>
                                @break
                            @case('datetime')
                                <td data-order="{{ $value?->format('Y-m-d H:i:s') }}">{{ $value?->format('d M, Y h:i A') ?? '—' }}</td>
                                @break
                            @case('money')
                                <td data-order="{{ $value }}">Rs. {{ number_format((float) $value, 2) }}</td>
                                @break
                            @case('boolean')
                                <td data-order="{{ (int) $value }}">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $value ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $value ? 'Yes' : 'No' }}</span>
                                </td>
                                @break
                            @case('status')
                                @php
                                    [$statusLabel, $statusClass] = match ($value) {
                                        'approved' => ['Approved', 'bg-green-100 text-green-700'],
                                        'rejected' => ['Disapproved', 'bg-red-100 text-red-700'],
                                        'returned' => ['Returned', 'bg-orange-100 text-orange-700'],
                                        // Pending again after being returned: the member fixed and resent it.
                                        default => filled($row->review_note) ? ['Resubmitted', 'bg-blue-100 text-blue-700'] : ['Pending', 'bg-gold-100 text-gold-700'],
                                    };
                                @endphp
                                <td data-order="{{ $statusLabel }}" data-search="{{ $statusLabel }}">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                    @if (filled($row->review_note) && $value !== 'approved')
                                    <div class="mt-1 max-w-[16rem] whitespace-normal text-xs text-gray-500">Note: {{ Str::limit($row->review_note, 80) }}</div>
                                    @endif
                                </td>
                                @break
                            @case('file')
                                <td>
                                    @if ($value)
                                    <a href="{{ $value }}" target="_blank" rel="noopener" class="font-semibold text-navy hover:underline">Open</a>
                                    @else
                                    <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                @break
                            @case('roles')
                                <td>
                                    @forelse ($row->roles as $role)
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize {{ $role->name === 'admin' ? 'bg-maroon-100 text-maroon' : 'bg-gold-100 text-gold-700' }}">{{ $role->label }}</span>
                                    @empty
                                    <span class="text-gray-300">—</span>
                                    @endforelse
                                </td>
                                @break
                            @default
                                <td class="{{ $col['class'] ?? '' }}">{{ filled($value) ? Str::limit(strip_tags((string) $value), $col['limit'] ?? 70) : '—' }}</td>
                        @endswitch
                    @endforeach
                    <td class="whitespace-nowrap text-right">
                        @if ($canApprove)
                            @if ($row->status !== 'approved')
                            <form method="POST" action="{{ route('dashboard.'.$key.'.approve', $row->getKey()) }}" class="inline">
                                @csrf
                                <button type="submit" class="mr-3 text-xs font-semibold text-green-700 hover:underline">Approve</button>
                            </form>
                            @endif
                            @if (! in_array($row->status, ['rejected', 'returned'], true))
                                @if ($rejectReason)
                                <button type="button" data-reject="{{ route('dashboard.'.$key.'.reject', $row->getKey()) }}" data-name="{{ data_get($row, $columns[$firstTextColumn ?: 0]['field']) }}" class="mr-3 text-xs font-semibold text-orange-600 hover:underline">{{ $rejectLabel }}</button>
                                @else
                                <form method="POST" action="{{ route('dashboard.'.$key.'.reject', $row->getKey()) }}" class="inline" onsubmit="return confirm('{{ $rejectLabel }} this {{ Str::lower($cfg['singular']) }}? It will be hidden from the site.')">
                                    @csrf
                                    <button type="submit" class="mr-3 text-xs font-semibold text-orange-600 hover:underline">{{ $rejectLabel }}</button>
                                </form>
                                @endif
                            @endif
                        @endif
                        @if ($readonly)
                        <a href="{{ route('dashboard.'.$key.'.show', $row->getKey()) }}" class="mr-3 text-xs font-semibold text-navy hover:underline">View</a>
                        @elseif ($canEdit)
                        <a href="{{ route('dashboard.'.$key.'.edit', $row->getKey()) }}" class="mr-3 text-xs font-semibold text-navy hover:underline">Edit</a>
                        @endif
                        @if ($canDelete)
                        <form method="POST" action="{{ route('dashboard.'.$key.'.destroy', $row->getKey()) }}" class="inline" onsubmit="return confirm('Delete this {{ Str::lower($cfg['singular']) }}? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($canApprove && $rejectReason)
    {{-- One shared dialog asking why; buttons with data-reject="{url}" open it. --}}
    <dialog id="reject-dialog" class="w-[92vw] max-w-md rounded-lg p-0 shadow-2xl backdrop:bg-black/50">
        <form method="POST" action="#" id="reject-form" class="p-5">
            @csrf
            <h3 class="text-lg font-bold text-navy">{{ $rejectLabel }} {{ Str::lower($cfg['singular']) }}</h3>
            <p class="mt-1 text-sm text-gray-600">You are returning <strong id="reject-name"></strong> to the person who added it. They will see your note, can correct it, and send it again.</p>
            <label for="reject-reason" class="mt-4 block text-sm font-semibold text-gray-700">What needs to be fixed? <span class="text-red-500">*</span></label>
            <textarea id="reject-reason" name="reason" rows="3" maxlength="500" required class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon" placeholder="e.g. The amount does not match the bank receipt."></textarea>
            <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" id="reject-cancel" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="rounded-md bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-700">{{ $rejectLabel }}</button>
            </div>
        </form>
    </dialog>
    @endif

    @push('scripts')
    @if ($canApprove && $rejectReason)
    <script>
        (function () {
            const dialog = document.getElementById('reject-dialog');
            const form = document.getElementById('reject-form');

            // Delegated so it keeps working for rows DataTables re-renders.
            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-reject]');
                if (!button) return;
                form.action = button.dataset.reject;
                document.getElementById('reject-name').textContent = button.dataset.name || 'this entry';
                document.getElementById('reject-reason').value = '';
                dialog.showModal();
            });

            document.getElementById('reject-cancel').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
        })();
    </script>
    @endif
    <script>
        new DataTable('#resource-table', {
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            columnDefs: [
                @if ($firstTextColumn !== false)
                { responsivePriority: 1, targets: {{ $firstTextColumn }} },
                @endif
                { responsivePriority: 2, targets: -1 },
                { orderable: false, targets: {{ Js::from($nonOrderable->push(count($columns))) }} },
            ],
            language: { search: '', searchPlaceholder: 'Search…', emptyTable: 'Nothing here yet.' },
        });
    </script>
    @endpush
</x-layouts.dashboard>
