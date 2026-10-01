<x-layouts.dashboard title="Roles">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-navy">🛡️ Roles</h2>
            <p class="text-sm text-gray-500">A role is a set of permissions. Give a role to a user under Users.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            @can('roles.view')
            <a href="{{ route('dashboard.permissions.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-navy hover:bg-gray-50">🔑 Permission Matrix</a>
            @endcan
            @can('roles.create')
            <a href="{{ route('dashboard.roles.create') }}" class="btn-maroon justify-center">+ Add Role</a>
            @endcan
        </div>
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif
    @if (session('dashboard-error'))
    <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
    @endif

    <div class="mt-5 overflow-x-auto rounded-lg bg-white shadow-md">
        <table class="w-full text-left text-sm">
            <thead class="border-b bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Permissions</th>
                    <th class="px-4 py-3">Users</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($roles as $role)
                <tr>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-navy">{{ $role->label }}</div>
                        @if ($role->isAdmin())
                        <div class="text-xs text-gray-500">Built-in · full access to everything</div>
                        @elseif ($role->isSystem())
                        <div class="text-xs text-gray-500">Built-in · given to everyone who registers</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($role->isAdmin())
                        <span class="rounded-full bg-maroon-100 px-2 py-0.5 text-xs font-semibold text-maroon">All</span>
                        @else
                        <span class="rounded-full bg-gold-100 px-2 py-0.5 text-xs font-semibold text-gold-700">{{ $role->permissions_count }} / {{ $total }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ number_format($role->users_count) }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        @if (! $role->isAdmin())
                            @can('roles.edit')
                            <a href="{{ route('dashboard.roles.edit', $role) }}" class="mr-3 text-xs font-semibold text-navy hover:underline">Edit</a>
                            @endcan
                            @if (! $role->isSystem())
                            @can('roles.delete')
                            <form method="POST" action="{{ route('dashboard.roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Delete the role {{ $role->label }}? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                            </form>
                            @endcan
                            @endif
                        @else
                        <span class="text-xs text-gray-400">Locked</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.dashboard>
