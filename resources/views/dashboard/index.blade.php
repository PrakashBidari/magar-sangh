<x-layouts.dashboard title="Overview">
    <div class="card mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=001F5B&color=fff&size=128" alt="{{ $user->name }}" class="h-16 w-16 rounded-full object-cover">
        <div class="min-w-0">
            <div class="text-lg font-bold text-navy">Welcome, {{ $user->name }}</div>
            <div class="truncate text-sm text-gray-500">{{ $user->email }}</div>
        </div>
        <div class="sm:ml-auto">
            <span class="rounded-full bg-maroon-50 px-3 py-1 text-xs font-semibold capitalize text-maroon">{{ $user->getRoleNames()->first() }}</span>
        </div>
    </div>

    @role('admin')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
        <a href="{{ route($stat['route']) }}" class="card transition hover:shadow-lg">
            <div class="text-xs font-semibold uppercase text-gray-400">{{ $stat['label'] }}</div>
            <div class="mt-2 text-2xl font-extrabold sm:text-3xl {{ ($stat['accent'] ?? false) ? 'text-maroon' : 'text-navy' }}">{{ $stat['value'] }}</div>
        </a>
        @endforeach
    </div>

    <h2 class="mb-3 mt-8 text-sm font-bold uppercase tracking-wider text-gray-500">Quick add</h2>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        @foreach (['news', 'articles', 'events', 'notifications', 'publications', 'gallery-photos', 'gallery-videos', 'committee', 'sister-organizations', 'donations'] as $key)
        <a href="{{ route('dashboard.'.$key.'.create') }}" class="card flex items-center gap-2 p-4 text-sm font-semibold text-navy transition hover:shadow-lg">
            <span>{{ config('admin.resources.'.$key.'.icon') }}</span>
            <span class="truncate">+ {{ config('admin.resources.'.$key.'.singular') }}</span>
        </a>
        @endforeach
    </div>
    @else
    <div class="card">
        <p class="text-sm text-gray-600">You are signed in as a member. Content management tools are available to administrators only.</p>
        <a href="{{ route('home') }}" class="btn-maroon mt-4">Visit the website</a>
    </div>
    @endrole
</x-layouts.dashboard>
