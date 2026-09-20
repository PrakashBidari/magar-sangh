@php
    $navItem = 'font-heading flex h-full items-center gap-1 px-4 py-3 text-sm font-semibold uppercase tracking-wide transition hover:bg-maroon';
    $isActive = fn (...$patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p)) ? 'bg-maroon' : '';
@endphp
<nav id="main-nav" class="sticky top-0 z-40 bg-navy text-white shadow-lg">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4">
        <button id="nav-hamburger" type="button" class="flex items-center justify-center p-3 md:hidden" aria-label="Toggle menu" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <ul id="nav-menu" class="hidden w-full flex-col md:flex md:w-auto md:flex-row md:items-stretch md:self-stretch">
            <li>
                <a href="{{ route('home') }}" class="{{ $navItem }} {{ $isActive('home') }}">Home</a>
            </li>

            <li class="nav-dropdown relative">
                <button type="button" class="nav-dropdown-toggle {{ $navItem }} {{ $isActive('about.*') }} w-full justify-between md:w-auto">
                    About Us
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <ul class="nav-dropdown-menu static mt-0 hidden w-full flex-col bg-maroon-800 text-sm md:absolute md:left-0 md:w-64 md:bg-white md:text-navy md:shadow-xl">
                    <li><a href="{{ route('about.index') }}#history" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Our History</a></li>
                    <li><a href="{{ route('about.index') }}#mission" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Mission &amp; Vision</a></li>
                    <li><a href="{{ route('about.committee') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Central Committee</a></li>
                    <li><a href="{{ route('about.constitution') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Constitution</a></li>
                </ul>
            </li>

            <li class="nav-dropdown relative">
                <button type="button" class="nav-dropdown-toggle {{ $navItem }} {{ $isActive('media.*') }} w-full justify-between md:w-auto">
                    Media &amp; Information
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <ul class="nav-dropdown-menu static mt-0 hidden w-full flex-col bg-maroon-800 text-sm md:absolute md:left-0 md:w-64 md:bg-white md:text-navy md:shadow-xl">
                    <li><a href="{{ route('media.news.index') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">News</a></li>
                    <li><a href="{{ route('media.articles.index') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Articles</a></li>
                    <li><a href="{{ route('media.notifications.index') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Notifications</a></li>
                    <li><a href="{{ route('media.publications.index') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Publications</a></li>
                    <li><a href="{{ route('media.events.index') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Events</a></li>
                </ul>
            </li>

            <li class="nav-dropdown relative">
                <button type="button" class="nav-dropdown-toggle {{ $navItem }} {{ $isActive('gallery.*') }} w-full justify-between md:w-auto">
                    Gallery
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <ul class="nav-dropdown-menu static mt-0 hidden w-full flex-col bg-maroon-800 text-sm md:absolute md:left-0 md:w-56 md:bg-white md:text-navy md:shadow-xl">
                    <li><a href="{{ route('gallery.photos') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Photo Gallery</a></li>
                    <li><a href="{{ route('gallery.videos') }}" class="block px-5 py-3 hover:bg-maroon-700 md:hover:bg-gray-100">Video Gallery</a></li>
                </ul>
            </li>

            <li>
                <a href="{{ route('donation-list') }}" class="{{ $navItem }} {{ $isActive('donation-list') }} flex-col !items-start justify-center leading-tight">
                    <span>Donation List</span>
                    <span class="text-[10px] font-normal normal-case text-gold-300">Lakhan Thapa Pratisthan</span>
                </a>
            </li>

            <li>
                <a href="{{ route('contact') }}" class="{{ $navItem }} {{ $isActive('contact') }}">Contact Us</a>
            </li>

            @auth
            <li>
                <a href="{{ route('dashboard.index') }}" class="{{ $navItem }}">Dashboard</a>
            </li>
            @endauth
        </ul>
    </div>
</nav>
