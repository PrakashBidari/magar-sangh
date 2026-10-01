@php $actions = \App\Support\Permissions::ACTIONS; @endphp
<x-layouts.dashboard title="Permission Matrix">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-navy">🔑 Permission Matrix</h2>
            <p class="text-sm text-gray-500">Every role side by side. Tick boxes and save to update all roles at once. Admin always has everything.</p>
        </div>
        <a href="{{ route('dashboard.roles.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to Roles</a>
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif
    @if ($errors->any())
    <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    @php $canEdit = auth()->user()->can('roles.edit'); @endphp
    <form method="POST" action="{{ route('dashboard.permissions.update') }}" id="matrix-form" class="mt-5">
        @csrf
        @method('PUT')

        <div class="overflow-x-auto rounded-lg bg-white shadow-md">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-2 text-left">Section</th>
                        <th class="px-4 py-2 text-left">Action</th>
                        <th class="px-3 py-2 text-center">Admin</th>
                        @foreach ($roles as $role)
                        <th class="px-3 py-2 text-center">
                            <label class="inline-flex cursor-pointer flex-col items-center gap-1 normal-case">
                                <span class="font-bold text-navy">{{ $role->label }}</span>
                                @if ($canEdit)
                                <input type="checkbox" data-role-toggle="{{ $role->id }}" class="rounded border-gray-300 text-navy focus:ring-navy" title="Tick everything for {{ $role->label }}">
                                @endif
                            </label>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groups as $group)
                    <tr class="bg-navy-50">
                        <td colspan="{{ $roles->count() + 3 }}" class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-navy">{{ $group['label'] }}</td>
                    </tr>
                    @foreach ($group['sections'] as $sectionKey => $section)
                    @foreach ($section['actions'] as $action)
                    @php $permission = $sectionKey.'.'.$action; @endphp
                    <tr class="border-t {{ $loop->first ? 'border-gray-200' : 'border-gray-100' }}">
                        @if ($loop->first)
                        <td rowspan="{{ count($section['actions']) }}" class="whitespace-nowrap border-r border-gray-100 px-4 py-1.5 align-top font-semibold text-gray-700">{{ $section['icon'] }} {{ $section['label'] }}</td>
                        @endif
                        <td class="whitespace-nowrap px-4 py-1.5 text-gray-500">{{ $actions[$action] }}</td>
                        <td class="px-3 py-1.5 text-center">
                            <input type="checkbox" checked disabled class="h-4 w-4 rounded border-gray-300 text-gray-400" aria-label="Admin: {{ $permission }}">
                        </td>
                        @foreach ($roles as $role)
                        <td class="px-3 py-1.5 text-center">
                            <input type="checkbox" name="permissions[{{ $role->id }}][]" value="{{ $permission }}" data-role="{{ $role->id }}"
                                aria-label="{{ $role->label }}: {{ $actions[$action] }} {{ $section['label'] }}"
                                class="h-4 w-4 rounded border-gray-300 text-maroon focus:ring-maroon"
                                @checked($role->permissions->contains('name', $permission)) @disabled(! $canEdit)>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canEdit)
        <div class="mt-5 flex justify-end">
            <button type="submit" class="btn-maroon justify-center">Save All Permissions</button>
        </div>
        @endif
    </form>

    @if ($canEdit)
    @push('scripts')
    <script>
        (function () {
            const form = document.getElementById('matrix-form');
            const refresh = () => form.querySelectorAll('[data-role-toggle]').forEach((toggle) => {
                const boxes = [...form.querySelectorAll('input[data-role="' + toggle.dataset.roleToggle + '"]')];
                toggle.checked = boxes.length > 0 && boxes.every((b) => b.checked);
            });

            form.addEventListener('change', (event) => {
                if (event.target.matches('[data-role-toggle]')) {
                    form.querySelectorAll('input[data-role="' + event.target.dataset.roleToggle + '"]').forEach((b) => (b.checked = event.target.checked));
                }
                refresh();
            });

            refresh();
        })();
    </script>
    @endpush
    @endif
</x-layouts.dashboard>
