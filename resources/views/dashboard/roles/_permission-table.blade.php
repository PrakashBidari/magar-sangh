{{-- Permission checkboxes for one role: a row per dashboard section, a column per action. --}}
<div class="card !p-0" data-permission-table>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3">
        <div>
            <div class="font-bold text-navy">Permissions</div>
            <p class="text-xs text-gray-500">Tick what this role may do. <b>View</b> is needed to open a page at all.</p>
        </div>
        <div class="flex gap-2 text-xs font-semibold">
            <button type="button" data-check-all class="rounded-md border border-gray-300 px-3 py-1.5 text-navy hover:bg-gray-50">Select all</button>
            <button type="button" data-uncheck-all class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-600 hover:bg-gray-50">Clear all</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-2 text-left">Section</th>
                    @foreach ($actions as $action => $label)
                    <th class="px-2 py-2 text-center">
                        <label class="inline-flex cursor-pointer flex-col items-center gap-1">
                            {{ $label }}
                            <input type="checkbox" data-column-toggle="{{ $action }}" class="rounded border-gray-300 text-maroon focus:ring-maroon" title="Tick {{ $label }} for every section">
                        </label>
                    </th>
                    @endforeach
                    <th class="px-2 py-2 text-center">All</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)
                <tr class="bg-navy-50">
                    <td colspan="{{ count($actions) + 2 }}" class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-navy">{{ $group['label'] }}</td>
                </tr>
                @foreach ($group['sections'] as $sectionKey => $section)
                <tr class="border-t" data-permission-row>
                    <td class="whitespace-nowrap px-4 py-2 font-semibold text-gray-700">{{ $section['icon'] }} {{ $section['label'] }}</td>
                    @foreach ($actions as $action => $label)
                    <td class="px-2 py-2 text-center">
                        @if (in_array($action, $section['actions'], true))
                        @php $permission = $sectionKey.'.'.$action; @endphp
                        <input type="checkbox" name="{{ $inputName ?? 'permissions[]' }}" value="{{ $permission }}" data-action="{{ $action }}"
                            aria-label="{{ $label }} {{ $section['label'] }}"
                            class="h-4 w-4 rounded border-gray-300 text-maroon focus:ring-maroon" @checked(in_array($permission, $granted, true))>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="px-2 py-2 text-center">
                        <input type="checkbox" data-row-toggle aria-label="All {{ $section['label'] }}" class="h-4 w-4 rounded border-gray-300 text-navy focus:ring-navy">
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@once
@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[data-permission-table]').forEach((table) => {
            const boxes = () => table.querySelectorAll('input[data-action]');

            const refresh = () => {
                table.querySelectorAll('[data-permission-row]').forEach((row) => {
                    const inRow = [...row.querySelectorAll('input[data-action]')];
                    row.querySelector('[data-row-toggle]').checked = inRow.length > 0 && inRow.every((b) => b.checked);
                });
                table.querySelectorAll('[data-column-toggle]').forEach((toggle) => {
                    const inColumn = [...table.querySelectorAll('input[data-action="' + toggle.dataset.columnToggle + '"]')];
                    toggle.checked = inColumn.length > 0 && inColumn.every((b) => b.checked);
                });
            };

            const setAll = (list, checked) => list.forEach((b) => (b.checked = checked));

            table.querySelector('[data-check-all]').addEventListener('click', () => { setAll(boxes(), true); refresh(); });
            table.querySelector('[data-uncheck-all]').addEventListener('click', () => { setAll(boxes(), false); refresh(); });

            table.addEventListener('change', (event) => {
                const target = event.target;

                if (target.matches('[data-row-toggle]')) {
                    setAll(target.closest('tr').querySelectorAll('input[data-action]'), target.checked);
                } else if (target.matches('[data-column-toggle]')) {
                    setAll(table.querySelectorAll('input[data-action="' + target.dataset.columnToggle + '"]'), target.checked);
                } else if (target.matches('input[data-action]') && target.checked && target.dataset.action !== 'view') {
                    // Doing anything in a section needs View too.
                    const view = target.closest('tr').querySelector('input[data-action="view"]');
                    if (view) view.checked = true;
                }

                refresh();
            });

            refresh();
        });
    })();
</script>
@endpush
@endonce
