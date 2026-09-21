{{-- One hero slide. $slide is null when no slides exist yet, so the homepage still shows its default banner. --}}
@php
    $titleNp = $slide?->title_np ?: 'हाम्रो भाषा, हाम्रो संस्कृति, हाम्रो पहिचान – हाम्रो गौरव';
    $titleEn = $slide?->title_en ?: 'Our Language, Our Culture, Our Identity – Our Pride';
    $text = $slide?->description ?: $settings->about_short_en;
@endphp
<div class="swiper-slide">
    <div class="relative h-[520px] w-full md:h-[620px]">
        @if ($slide)
        <img src="{{ $slide->image_url }}" alt="{{ $titleEn }}" class="absolute inset-0 h-full w-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-black/20"></div>

        <div class="absolute inset-0 flex items-center">
            <div class="mx-auto w-full max-w-7xl px-4 pb-10">
                <div class="max-w-2xl">
                    <h1 class="np text-2xl font-extrabold leading-snug text-maroon-300 drop-shadow md:text-4xl">{{ $titleNp }}</h1>
                    <h2 class="mt-2 text-xl font-extrabold uppercase tracking-wide text-white drop-shadow md:text-3xl">{{ $titleEn }}</h2>
                    @if ($text)
                    <p class="mt-4 text-sm text-gray-200 md:text-base">{{ $text }}</p>
                    @endif
                    <div class="mt-6 flex flex-wrap gap-4">
                        <a href="{{ route('register') }}" class="btn-maroon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-3a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0"/></svg>
                            Become a Member
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
