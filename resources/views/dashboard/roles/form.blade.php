@php
    $editing = $role->exists;
    $title = $editing ? 'Edit Role: '.$role->label : 'Add Role';
    $actions = \App\Support\Permissions::ACTIONS;
@endphp
<x-layouts.dashboard :title="$title">
    <div class="mx-auto max-w-5xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-navy">🛡️ {{ $title }}</h2>
            <a href="{{ route('dashboard.roles.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to Roles</a>
        </div>

        @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold">Please fix the following:</div>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $editing ? route('dashboard.roles.update', $role) : route('dashboard.roles.store') }}" id="role-form" class="space-y-5">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="card">
                <label for="f-name" class="text-sm font-semibold text-gray-700">Role Name @unless ($role->isSystem()) <span class="text-red-500">*</span> @endunless</label>
                <input id="f-name" name="name" type="text" value="{{ old('name', $role->name) }}" maxlength="100" placeholder="e.g. News Editor"
                    class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon disabled:bg-gray-100 disabled:text-gray-500"
                    @disabled($role->isSystem()) @required(! $role->isSystem())>
                @if ($role->isSystem())
                <p class="mt-1 text-xs text-gray-400">Built-in role: the name cannot be changed, but you can choose what it may do.</p>
                @endif
            </div>

            @include('dashboard.roles._permission-table', ['groups' => $groups, 'granted' => $granted, 'actions' => $actions])

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('dashboard.roles.index') }}" class="rounded-md border border-gray-300 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="btn-maroon justify-center">{{ $editing ? 'Save Changes' : 'Create Role' }}</button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
