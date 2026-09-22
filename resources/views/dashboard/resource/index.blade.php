@php
    $readonly = $cfg['readonly'] ?? false;
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
        @unless ($readonly)
        <a href="{{ route('dashboard.'.$key.'.create') }}" class="btn-maroon justify-center">+ Add {{ $cfg['singular'] }}</a>
        @endunless
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif
    @if (session('dashboard-error'))
    <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
    @endif

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
                                    @if ($value)
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
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize {{ $role->name === 'admin' ? 'bg-maroon-100 text-maroon' : 'bg-gold-100 text-gold-700' }}">{{ $role->name }}</span>
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
                        @if ($readonly)
                        <a href="{{ route('dashboard.'.$key.'.show', $row->getKey()) }}" class="mr-3 text-xs font-semibold text-navy hover:underline">View</a>
                        @else
                        <a href="{{ route('dashboard.'.$key.'.edit', $row->getKey()) }}" class="mr-3 text-xs font-semibold text-navy hover:underline">Edit</a>
                        @endif
                        <form method="POST" action="{{ route('dashboard.'.$key.'.destroy', $row->getKey()) }}" class="inline" onsubmit="return confirm('Delete this {{ Str::lower($cfg['singular']) }}? This cannot be undone.')">
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

    @push('scripts')
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
