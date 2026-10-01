@props(['title' => 'Dashboard'])
@php
    $groups = config('admin.groups');
    $resources = collect(config('admin.resources'))->map(fn ($r, $k) => $r + ['key' => $k])->groupBy('group');

    // Sidebar entries per group: config resources plus the hand-built membership pages.
    $me = auth()->user();

    // Every entry names the permission it needs ('can'); entries the user lacks are dropped below.
    $navItems = $resources->map(fn ($items) => $items->map(fn ($r) => [
        'label' => $r['label'],
        'icon' => $r['icon'],
        'href' => route('dashboard.'.$r['key'].'.index'),
        'active' => request()->routeIs('dashboard.'.$r['key'].'.*'),
        // Sections that need approval show how many entries are waiting.
        'badge' => ($r['approvable'] ?? false) && $me->can($r['key'].'.approve') ? ($r['model']::where('status', 'pending')->count() ?: null) : null,
        'can' => $r['key'].'.view',
    ])->values());

    // "My Donations" dropdown, open to every signed-in user.
    $myDonationsOpen = request()->routeIs('dashboard.my-donations.*');
    $myReturned = \App\Models\Donation::whereBelongsTo($me)->where('status', 'returned')->count();
    $myDonationLinks = [
        ['label' => 'Add Donation', 'icon' => '➕', 'href' => route('dashboard.my-donations.create'), 'active' => request()->routeIs('dashboard.my-donations.create'), 'badge' => null],
        ['label' => 'View & Edit Donations', 'icon' => '📋', 'href' => route('dashboard.my-donations.index'), 'active' => request()->routeIs('dashboard.my-donations.index', 'dashboard.my-donations.show', 'dashboard.my-donations.edit'), 'badge' => $myReturned ?: null],
    ];

    // Application detail / edit pages highlight the list the application belongs to.
    $viewing = request()->routeIs('dashboard.membership.show', 'dashboard.membership.edit') ? request()->route('membership')?->status : null;
    $pendingCount = $me->can('membership-applications.view') ? \App\Models\Membership::where('status', 'pending')->count() : 0;
    $navItems['membership'] = collect($navItems['membership'] ?? [])->merge([
        ['label' => 'Pending Applications', 'icon' => '⏳', 'href' => route('dashboard.membership.pending'), 'active' => request()->routeIs('dashboard.membership.pending') || $viewing === 'pending', 'badge' => $pendingCount ?: null, 'can' => 'membership-applications.view'],
        ['label' => 'Approved Members', 'icon' => '✅', 'href' => route('dashboard.membership.approved'), 'active' => request()->routeIs('dashboard.membership.approved') || $viewing === 'approved', 'badge' => null, 'can' => 'membership-applications.view'],
        ['label' => 'Disapproved', 'icon' => '⛔', 'href' => route('dashboard.membership.rejected'), 'active' => request()->routeIs('dashboard.membership.rejected') || $viewing === 'rejected', 'badge' => null, 'can' => 'membership-applications.view'],
        ['label' => 'Membership Settings', 'icon' => '⚙️', 'href' => route('dashboard.membership.settings'), 'active' => request()->routeIs('dashboard.membership.settings'), 'badge' => null, 'can' => 'membership-settings.manage'],
    ])->values();

    // Sifaris requests, one link per status (detail pages highlight the list they belong to).
    $viewingSifaris = request()->routeIs('dashboard.sifaris.show', 'dashboard.sifaris.edit', 'dashboard.sifaris.letter') ? request()->route('sifaris')?->status : null;
    $pendingSifaris = $me->can('sifaris.view') ? \App\Models\SifarisRequest::where('status', 'pending')->count() : 0;
    $navItems['sifaris'] = collect([
        ['label' => 'Pending Sifaris', 'icon' => '⏳', 'href' => route('dashboard.sifaris.pending'), 'active' => request()->routeIs('dashboard.sifaris.pending') || $viewingSifaris === 'pending', 'badge' => $pendingSifaris ?: null, 'can' => 'sifaris.view'],
        ['label' => 'Approved Sifaris', 'icon' => '✅', 'href' => route('dashboard.sifaris.approved'), 'active' => request()->routeIs('dashboard.sifaris.approved') || $viewingSifaris === 'approved', 'badge' => null, 'can' => 'sifaris.view'],
        ['label' => 'Disapproved', 'icon' => '⛔', 'href' => route('dashboard.sifaris.rejected'), 'active' => request()->routeIs('dashboard.sifaris.rejected') || $viewingSifaris === 'rejected', 'badge' => null, 'can' => 'sifaris.view'],
    ]);

    // Lakhan Thapa Pratisthan: the donation list plus the page content settings.
    $navItems['donation'] = collect($navItems['donation'] ?? [])->push(
        ['label' => 'Settings', 'icon' => '⚙️', 'href' => route('dashboard.donation-settings'), 'active' => request()->routeIs('dashboard.donation-settings'), 'badge' => null, 'can' => 'donation-settings.manage'],
    );

    // Accounting: the income & expense book, one link per view (the list page reads ?type=).
    $bookType = request()->routeIs('dashboard.accounting.edit') ? request()->route('transaction')?->type : request()->query('type');
    $onBook = request()->routeIs('dashboard.accounting.index', 'dashboard.accounting.edit');
    $navItems['accounting'] = collect([
        ['label' => 'All Entries', 'icon' => '📒', 'href' => route('dashboard.accounting.index'), 'active' => $onBook && ! in_array($bookType, ['income', 'expense'], true), 'badge' => null, 'can' => 'accounting.view'],
        ['label' => 'Income', 'icon' => '💰', 'href' => route('dashboard.accounting.index', ['type' => 'income']), 'active' => $onBook && $bookType === 'income', 'badge' => null, 'can' => 'accounting.view'],
        ['label' => 'Expense', 'icon' => '💸', 'href' => route('dashboard.accounting.index', ['type' => 'expense']), 'active' => $onBook && $bookType === 'expense', 'badge' => null, 'can' => 'accounting.view'],
        ['label' => 'Add Entry', 'icon' => '➕', 'href' => route('dashboard.accounting.create'), 'active' => request()->routeIs('dashboard.accounting.create'), 'badge' => null, 'can' => 'accounting.create'],
    ])->merge($navItems['accounting'] ?? []); // then Entry Categories (from config)

    // Roles & Permissions: roles, the all-roles matrix, then users (from config).
    $navItems['access'] = collect([
        ['label' => 'All Roles', 'icon' => '🛡️', 'href' => route('dashboard.roles.index'), 'active' => request()->routeIs('dashboard.roles.index', 'dashboard.roles.edit'), 'badge' => null, 'can' => 'roles.view'],
        ['label' => 'Add Role', 'icon' => '➕', 'href' => route('dashboard.roles.create'), 'active' => request()->routeIs('dashboard.roles.create'), 'badge' => null, 'can' => 'roles.create'],
        ['label' => 'Permission Matrix', 'icon' => '🔑', 'href' => route('dashboard.permissions.index'), 'active' => request()->routeIs('dashboard.permissions.*'), 'badge' => null, 'can' => 'roles.view'],
    ])->merge($navItems['access'] ?? []);

    $navItems = $navItems
        ->map(fn ($items) => $items->filter(fn ($item) => $me->can($item['can']))->values())
        ->filter(fn ($items) => $items->isNotEmpty());
@endphp
<!DOCTYPE html>
<html lang="en" translate="no" class="notranslate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Dashboard - {{ $siteSettings->site_name_en }}</title>
    @if ($siteSettings->logo_url)<link rel="icon" href="{{ $siteSettings->logo_url }}">@endif
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css">
    @include('partials.editor-fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/editor.css'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        <aside id="dashboard-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 shrink-0 -translate-x-full transform flex-col bg-gradient-to-b from-navy-800 to-navy text-white shadow-2xl transition-transform duration-300 ease-out md:sticky md:top-0 md:h-screen md:w-64 md:translate-x-0 md:shadow-none">
            {{-- Brand --}}
            <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 border-b border-white/10 px-5 py-4">
                @if ($siteSettings->logo_url)
                <img src="{{ $siteSettings->logo_url }}" alt="Logo" class="h-12 w-auto rounded-md bg-white object-contain p-1 ring-2 ring-gold/60">
                @endif
                <div class="min-w-0">
                    <div class="np truncate text-sm font-bold leading-tight">{{ $siteSettings->site_name_np }}</div>
                    <div class="text-[11px] font-semibold uppercase tracking-widest text-white">Dashboard</div>
                </div>
            </a>

            {{-- Navigation --}}
            <nav class="dash-scroll flex-1 space-y-1 overflow-y-auto px-3 pb-4 pt-3">
                <a href="{{ route('dashboard.index') }}" class="dash-link {{ request()->routeIs('dashboard.index') ? 'is-active' : '' }}">
                    <span class="dash-icon">📊</span> <span class="flex-1">Overview</span>
                </a>

                <a href="{{ route('dashboard.my-membership.show') }}" class="dash-link {{ request()->routeIs('dashboard.my-membership.*') ? 'is-active' : '' }}">
                    <span class="dash-icon">🎫</span> <span class="flex-1">My Membership</span>
                </a>

                @php $mySifarisActive = request()->routeIs('dashboard.my-sifaris.*') || (request()->routeIs('dashboard.sifaris.letter') && request()->route('sifaris')?->user_id === $me->id); @endphp
                <a href="{{ route('dashboard.my-sifaris.index') }}" class="dash-link {{ $mySifarisActive ? 'is-active' : '' }}">
                    <span class="dash-icon">📜</span> <span class="flex-1">My Sifaris</span>
                </a>

                <div class="nav-group {{ $myDonationsOpen ? 'is-open' : '' }}">
                    <button type="button" class="nav-group-toggle dash-link {{ $myDonationsOpen ? 'is-active' : '' }}" aria-expanded="{{ $myDonationsOpen ? 'true' : 'false' }}">
                        <span class="dash-icon">💝</span>
                        <span class="flex-1">My Donations</span>
                        @if ($myReturned)
                        <span class="rounded-full bg-gold px-2 py-0.5 text-[10px] font-extrabold text-navy" title="Returned for correction">{{ $myReturned }}</span>
                        @endif
                        <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="nav-group-panel">
                        <div class="nav-group-inner">
                            <div class="ml-7 mt-1 space-y-0.5 border-l border-white/15 pb-1 pl-3">
                                @foreach ($myDonationLinks as $link)
                                <a href="{{ $link['href'] }}" style="--i: {{ $loop->index }}" class="dash-sublink {{ $link['active'] ? 'is-active' : '' }}">
                                    <span class="w-5 text-center">{{ $link['icon'] }}</span> <span class="flex-1 truncate">{{ $link['label'] }}</span>
                                    @if ($link['badge'])
                                    <span class="rounded-full bg-gold px-1.5 text-[10px] font-extrabold text-navy">{{ $link['badge'] }}</span>
                                    @endif
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                @can('settings.manage')
                <a href="{{ route('dashboard.settings') }}" class="dash-link {{ request()->routeIs('dashboard.settings') ? 'is-active' : '' }}">
                    <span class="dash-icon">⚙️</span> <span class="flex-1">Site &amp; About Settings</span>
                </a>
                @endcan

                @if ($navItems->isNotEmpty())
                <div class="dash-section-label">Manage content</div>
                @endif

                @foreach ($groups as $groupKey => $groupLabel)
                    @continue(! $navItems->has($groupKey))
                    @php
                        $items = $navItems[$groupKey];
                        $groupActive = $items->contains('active', true);
                        $groupBadge = $items->sum('badge');
                    @endphp

                    @if ($items->count() === 1)
                        {{-- A group with a single page is just a link --}}
                        @php $res = $items->first(); @endphp
                        <a href="{{ $res['href'] }}" class="dash-link {{ $groupActive ? 'is-active' : '' }}">
                            <span class="dash-icon">{{ $res['icon'] }}</span> <span class="flex-1">{{ $res['label'] }}</span>
                        </a>
                    @else
                        <div class="nav-group {{ $groupActive ? 'is-open' : '' }}">
                            <button type="button" class="nav-group-toggle dash-link {{ $groupActive ? 'is-active' : '' }}" aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                                <span class="dash-icon">{{ $items->first()['icon'] }}</span>
                                <span class="flex-1">{{ $groupLabel }}</span>
                                @if ($groupBadge)
                                <span class="rounded-full bg-gold px-2 py-0.5 text-[10px] font-extrabold text-navy">{{ $groupBadge }}</span>
                                @else
                                <span class="dash-badge">{{ $items->count() }}</span>
                                @endif
                                <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="nav-group-panel">
                                <div class="nav-group-inner">
                                    <div class="ml-7 mt-1 space-y-0.5 border-l border-white/15 pb-1 pl-3">
                                        @foreach ($items as $res)
                                        <a href="{{ $res['href'] }}" style="--i: {{ $loop->index }}" class="dash-sublink {{ $res['active'] ? 'is-active' : '' }}">
                                            <span class="w-5 text-center">{{ $res['icon'] }}</span> <span class="flex-1 truncate">{{ $res['label'] }}</span>
                                            @if ($res['badge'])
                                            <span class="rounded-full bg-gold px-1.5 text-[10px] font-extrabold text-navy">{{ $res['badge'] }}</span>
                                            @endif
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            {{-- Signed-in user --}}
            <div class="border-t border-white/10 p-3">
                <div class="mb-2 flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gold/90 text-sm font-extrabold text-navy">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] capitalize text-white">{{ auth()->user()->getRoleNames()->first() }}</div>
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
