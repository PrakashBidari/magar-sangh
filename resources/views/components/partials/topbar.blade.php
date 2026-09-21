<div class="bg-maroon-700 text-white text-sm">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-4 gap-y-1 px-4 py-2">
        <div class="flex items-center gap-4">
            <a href="tel:{{ $siteSettings->phone }}" class="flex items-center gap-1.5 hover:text-gold-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.57.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.57 1 1 0 01-.25 1.01l-2.2 2.21z"/></svg>
                <span class="hidden sm:inline">{{ $siteSettings->phone }}</span>
            </a>
            <a href="mailto:{{ $siteSettings->email }}" class="flex items-center gap-1.5 hover:text-gold-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5z"/></svg>
                <span class="hidden sm:inline">{{ $siteSettings->email }}</span>
            </a>
        </div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
            @auth
            @php $activeMembership = auth()->user()->activeMembership()->with('type')->first(); @endphp
            @if ($activeMembership)
            <a href="{{ route('dashboard.my-membership.show') }}" title="Membership No. {{ $activeMembership->membership_number }}" class="flex max-w-[11rem] items-center gap-1.5 rounded-full bg-gold/90 px-2.5 py-0.5 text-xs font-bold text-navy hover:bg-gold sm:max-w-none">
                <i class="fa-solid fa-id-card shrink-0"></i>
                <span class="np truncate" data-np="{{ $activeMembership->type->name_np }}" data-en="{{ $activeMembership->type->name_en }}">{{ $activeMembership->type->name_np }}</span>
            </a>
            @endif
            @endauth
            <button type="button" id="lang-toggle" class="flex items-center gap-1 font-semibold">
                <span data-lang-option="np" class="lang-active">नेपाली</span>
                <span class="text-white/50">|</span>
                <span data-lang-option="en" class="text-white/70">English</span>
            </button>
            <div class="flex items-center gap-3 text-xs font-semibold sm:text-sm">
                @auth
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-1 rounded bg-white/15 px-2.5 py-1 hover:bg-white/25">
                    <i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="hover:text-gold-200">Logout</button>
                </form>
                @else
                <a href="{{ route('login') }}" class="hover:text-gold-200">Login</a>
                <a href="{{ route('register') }}" class="rounded bg-white/15 px-2.5 py-1 hover:bg-white/25">Register</a>
                @endauth
            </div>
            <div class="hidden items-center gap-3 sm:flex">
                @if($siteSettings->facebook_url)
                <a href="{{ $siteSettings->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook" class="hover:text-gold-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 9H15V6.5h-1.5C11.6 6.5 10 8.1 10 10.1V12H8v3h2v6h3v-6h2.1l.4-3H13v-1.6c0-.6.3-1.4.5-1.4z"/></svg>
                </a>
                @endif
                @if($siteSettings->instagram_url)
                <a href="{{ $siteSettings->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram" class="hover:text-gold-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c2.7 0 3 0 4.1.06 1.1.05 1.8.22 2.5.47.7.27 1.2.6 1.8 1.16.5.5.9 1.1 1.15 1.8.25.7.4 1.4.47 2.5.06 1.1.06 1.4.06 4.1s0 3-.06 4.1c-.06 1.1-.22 1.8-.47 2.5-.27.7-.6 1.2-1.16 1.8-.5.5-1.1.9-1.8 1.15-.7.25-1.4.4-2.5.47-1.1.06-1.4.06-4.1.06s-3 0-4.1-.06c-1.1-.06-1.8-.22-2.5-.47a5 5 0 01-1.8-1.16 5 5 0 01-1.15-1.8c-.25-.7-.4-1.4-.47-2.5C2 15 2 14.7 2 12s0-3 .06-4.1c.06-1.1.22-1.8.47-2.5.27-.7.6-1.2 1.16-1.8.5-.5 1.1-.9 1.8-1.15.7-.25 1.4-.4 2.5-.47C9 2 9.3 2 12 2zm0 5a5 5 0 100 10 5 5 0 000-10zm0 8.2a3.2 3.2 0 110-6.4 3.2 3.2 0 010 6.4zm5.2-8.4a1.2 1.2 0 100-2.4 1.2 1.2 0 000 2.4z"/></svg>
                </a>
                @endif
                @if($siteSettings->youtube_url)
                <a href="{{ $siteSettings->youtube_url }}" target="_blank" rel="noopener" aria-label="YouTube" class="hover:text-gold-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.6-.46-5.3a3 3 0 00-2.1-2.1C18.7 4 12 4 12 4s-6.7 0-8.44.6a3 3 0 00-2.1 2.1C1 8.4 1 12 1 12s0 3.6.46 5.3a3 3 0 002.1 2.1C5.3 20 12 20 12 20s6.7 0 8.44-.6a3 3 0 002.1-2.1C23 15.6 23 12 23 12zM10 15.5v-7l6 3.5z"/></svg>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
