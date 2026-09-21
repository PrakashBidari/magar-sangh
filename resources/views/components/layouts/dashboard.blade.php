@props(['title' => 'Dashboard'])
@php
    $groups = config('admin.groups');
    $resources = collect(config('admin.resources'))->map(fn ($r, $k) => $r + ['key' => $k])->groupBy('group');
@endphp
<!DOCTYPE html>
<html lang="en" translate="no" class="notranslate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Dashboard - {{ $siteSettings->site_name_en }}</title>
    <link rel="icon" href="{{ $siteSettings->logo_url }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/editor.css'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        <aside id="dashboard-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 shrink-0 -translate-x-full transform flex-col bg-gradient-to-b from-navy-800 to-navy text-white shadow-2xl transition-transform duration-300 ease-out md:sticky md:top-0 md:h-screen md:w-64 md:translate-x-0 md:shadow-none">
            {{-- Brand --}}
            <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 border-b border-white/10 px-5 py-4">
                <img src="{{ $siteSettings->logo_url }}" alt="Logo" class="h-11 w-11 rounded-full object-cover ring-2 ring-gold/60">
                <div class="min-w-0">
                    <div class="np truncate text-sm font-bold leading-tight">{{ $siteSettings->site_name_np }}</div>
                    <div class="text-[11px] font-semibold uppercase tracking-widest text-gold-300">Dashboard</div>
                </div>
            </a>

            {{-- Navigation --}}
            <nav class="dash-scroll flex-1 space-y-1 overflow-y-auto px-3 pb-4 pt-3">
                <a href="{{ route('dashboard.index') }}" class="dash-link {{ request()->routeIs('dashboard.index') ? 'is-active' : '' }}">
                    <span class="dash-icon">📊</span> <span class="flex-1">Overview</span>
                </a>

                @role('admin')
                <a href="{{ route('dashboard.settings') }}" class="dash-link {{ request()->routeIs('dashboard.settings') ? 'is-active' : '' }}">
                    <span class="dash-icon">⚙️</span> <span class="flex-1">Site &amp; About Settings</span>
                </a>

                <div class="dash-section-label">Manage content</div>

                @foreach ($groups as $groupKey => $groupLabel)
                    @continue(! $resources->has($groupKey))
                    @php
                        $items = $resources[$groupKey];
                        $groupActive = $items->contains(fn ($r) => request()->routeIs('dashboard.'.$r['key'].'.*'));
                    @endphp

                    @if ($items->count() === 1)
                        {{-- A group with a single page is just a link --}}
                        @php $res = $items->first(); @endphp
                        <a href="{{ route('dashboard.'.$res['key'].'.index') }}" class="dash-link {{ $groupActive ? 'is-active' : '' }}">
                            <span class="dash-icon">{{ $res['icon'] }}</span> <span class="flex-1">{{ $res['label'] }}</span>
                        </a>
                    @else
                        <div class="nav-group {{ $groupActive ? 'is-open' : '' }}">
                            <button type="button" class="nav-group-toggle dash-link {{ $groupActive ? 'is-active' : '' }}" aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                                <span class="dash-icon">{{ $items->first()['icon'] }}</span>
                                <span class="flex-1">{{ $groupLabel }}</span>
                                <span class="dash-badge">{{ $items->count() }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="nav-group-panel">
                                <div class="nav-group-inner">
                                    <div class="ml-7 mt-1 space-y-0.5 border-l border-white/15 pb-1 pl-3">
                                        @foreach ($items as $res)
                                        <a href="{{ route('dashboard.'.$res['key'].'.index') }}" style="--i: {{ $loop->index }}" class="dash-sublink {{ request()->routeIs('dashboard.'.$res['key'].'.*') ? 'is-active' : '' }}">
                                            <span class="w-5 text-center">{{ $res['icon'] }}</span> <span class="truncate">{{ $res['label'] }}</span>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                @endrole
            </nav>

            {{-- Signed-in user --}}
            <div class="border-t border-white/10 p-3">
                <div class="mb-2 flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gold/90 text-sm font-extrabold text-navy">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] capitalize text-white/50">{{ auth()->user()->getRoleNames()->first() }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-xs font-semibold transition hover:bg-white/20">🏠 Website</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-maroon px-3 py-2 text-xs font-semibold transition hover:bg-maroon-700">🚪 Logout</button>
                    </form>
                </div>
            </div>
        </aside>
        <div id="dashboard-overlay" class="pointer-events-none fixed inset-0 z-30 bg-black/50 opacity-0 backdrop-blur-[1px] transition-opacity duration-300 md:hidden"></div>

        {{-- MAIN --}}
        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 flex items-center justify-between gap-3 bg-white px-4 py-3 shadow-sm sm:px-5 sm:py-4">
                <button id="dashboard-sidebar-toggle" type="button" class="rounded-md p-2 hover:bg-gray-100 md:hidden" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="truncate text-base font-bold text-navy sm:text-lg">{{ $title }}</h1>
                <div class="flex shrink-0 items-center gap-2 text-sm">
                    <span class="hidden text-gray-500 sm:inline">{{ auth()->user()->name }}</span>
                    <span class="rounded-full bg-maroon-50 px-2 py-1 text-xs font-semibold capitalize text-maroon">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </header>

            <main class="p-4 sm:p-5">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>

    {{-- Shared rich text editor (Jodit, bundled by Vite and loaded only when a page needs it) --}}
    <script>
        window.NepalRichEditor = {
            /** Turn a <textarea> into a rich editor; `onChange(html)` fires on every edit. Resolves with the editor. */
            init(element, onChange) {
                return import(@json(Vite::asset('resources/js/editor.js')))
                    .then(() => window.createRichEditor(element, @json(route('dashboard.editor-upload')), onChange));
            },
        };
    </script>
    @stack('scripts')
</body>
</html>
