<header class="relative overflow-hidden bg-white">
    <div class="relative mx-auto grid max-w-7xl grid-cols-3 items-center gap-4 px-4 py-4">
        <div class="flex justify-start">
            <a href="{{ route('home') }}">
                @if ($siteSettings->logo_url)
                <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->site_name_en }} Logo" class="h-20 w-auto object-contain md:h-24">
                @endif
            </a>
        </div>
        <div class="text-center">
            <a href="{{ route('home') }}" class="block">
                <div class="np text-3xl font-extrabold text-maroon md:text-4xl lg:text-5xl">{{ $siteSettings->site_name_np }}</div>
                <div class="font-heading text-lg font-bold tracking-wide text-navy md:text-xl lg:text-2xl">{{ Str::upper($siteSettings->site_name_en) }}</div>
            </a>
            <div class="mt-1 space-y-0.5 text-[10px] font-semibold md:text-xs">
                <div class="np text-maroon">{{ $siteSettings->tagline_np }}</div>
                <div class="text-navy">{{ $siteSettings->tagline_en }}</div>
            </div>
        </div>
        <div class="flex justify-end">
            @if ($siteSettings->flag_url)
            <img src="{{ $siteSettings->flag_url }}" alt="Nepal Flag" class="h-14 w-auto object-contain md:h-16">
            @endif
        </div>
    </div>
</header>
