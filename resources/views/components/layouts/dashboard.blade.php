@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="en" translate="no" class="notranslate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <title>{{ $title }} - Dashboard - {{ $siteSettings->site_name_en }}</title>
    <link rel="icon" href="{{ $siteSettings->logo_url }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        <aside id="dashboard-sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform bg-navy text-white transition-transform duration-200 md:static md:translate-x-0">
            <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
                <img src="{{ $siteSettings->logo_url }}" alt="Logo" class="h-10 w-10 rounded-full object-cover">
                <div class="np text-sm font-bold leading-tight">{{ $siteSettings->site_name_np }}</div>
            </div>
            <nav class="mt-4 space-y-1 px-3 text-sm">
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 font-semibold hover:bg-white/10 {{ request()->routeIs('dashboard.index') ? 'bg-white/10' : '' }}">
                    <span>📊</span> Overview
                </a>
                @role('admin')
                <a href="{{ route('dashboard.users') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 font-semibold hover:bg-white/10 {{ request()->routeIs('dashboard.users*') ? 'bg-white/10' : '' }}">
                    <span>👥</span> Users
                </a>
                <a href="{{ route('dashboard.donations') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 font-semibold hover:bg-white/10 {{ request()->routeIs('dashboard.donations') ? 'bg-white/10' : '' }}">
                    <span>💰</span> Donation List
                </a>
                <a href="{{ route('dashboard.settings') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 font-semibold hover:bg-white/10 {{ request()->routeIs('dashboard.settings') ? 'bg-white/10' : '' }}">
                    <span>⚙️</span> Settings
                </a>
                @endrole
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 font-semibold hover:bg-white/10">
                    <span>🏠</span> Visit Site
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left font-semibold hover:bg-white/10">
                        <span>🚪</span> Logout
                    </button>
                </form>
            </nav>
        </aside>
        <div id="dashboard-overlay" class="fixed inset-0 z-30 hidden bg-black/40 md:hidden"></div>

        {{-- MAIN --}}
        <div class="flex-1 md:pl-0">
            <header class="flex items-center justify-between bg-white px-5 py-4 shadow-sm">
                <button id="dashboard-sidebar-toggle" type="button" class="rounded-md p-2 hover:bg-gray-100 md:hidden" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-bold text-navy">{{ $title }}</h1>
                <div class="flex items-center gap-2 text-sm">
                    <span class="hidden sm:inline text-gray-500">{{ auth()->user()->name }}</span>
                    <span class="rounded-full bg-maroon-50 px-2 py-1 text-xs font-semibold text-maroon">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </header>

            <main class="p-5">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
