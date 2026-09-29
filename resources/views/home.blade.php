<x-layouts.app title="Home">

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-navy-900">
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                @forelse ($heroSlides as $slide)
                    @include('partials.hero-slide', ['slide' => $slide])
                @empty
                    @include('partials.hero-slide', ['slide' => null])
                @endforelse
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    {{-- PRESIDENT MESSAGE --}}
    @if ($settings->president_message_np || $settings->president_name_np || $settings->president_photo_url)
    <section class="mx-auto max-w-7xl px-4 py-14">
        <div class="card grid grid-cols-1 items-center gap-8 md:grid-cols-3">
            <div class="flex justify-center md:col-span-1">
                @if ($settings->president_photo_url)
                <img src="{{ $settings->president_photo_url }}" alt="{{ $settings->president_name_np }}" class="h-48 w-48 rounded-full border-4 border-gold object-cover shadow-lg">
                @endif
            </div>
            <div class="relative md:col-span-2">
                <svg class="absolute -left-2 -top-6 h-14 w-14 text-maroon-100" fill="currentColor" viewBox="0 0 24 24"><path d="M7 7h5v5c0 3-2 5-5 5v-2c1.5 0 3-1 3-3H7V7zm9 0h5v5c0 3-2 5-5 5v-2c1.5 0 3-1 3-3h-3V7z"/></svg>
                <p class="np relative text-lg leading-relaxed text-gray-700 md:text-xl">
                    {{ $settings->president_message_np }}
                </p>
                <p class="np mt-4 text-right font-bold text-maroon">– {{ $settings->president_name_np }}</p>
            </div>
        </div>
    </section>
    @endif

    {{-- STATS --}}
    <section class="bg-navy py-12 text-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 divide-white/20 px-4 text-center sm:divide-x-2 md:grid-cols-5">
            @php
                $stats = [
                    ['icon' => 'fa-solid fa-users', 'value' => number_format($settings->stat_members).'+', 'label' => 'Members'],
                    ['icon' => 'fa-solid fa-map-location-dot', 'value' => $settings->stat_districts, 'label' => 'District Chapters'],
                    ['icon' => 'fa-solid fa-earth-asia', 'value' => $settings->stat_countries.'+', 'label' => 'Countries Connected'],
                    ['icon' => 'fa-solid fa-handshake', 'value' => $settings->stat_sister_orgs, 'label' => 'Sister Organizations'],
                    ['icon' => 'fa-solid fa-heart', 'value' => '∞', 'label' => 'Countless Lives Impacted', 'wide' => true],
                ];
            @endphp
            @foreach ($stats as $stat)
            <div class="px-4 py-2 {{ $stat['wide'] ?? false ? 'col-span-2 md:col-span-1' : '' }}">
                <div class="mx-auto flex h-20 w-20 items-center justify-center text-4xl text-gold-300"><i class="{{ $stat['icon'] }}"></i></div>
                <div class="mt-2 text-3xl font-extrabold text-gold-300">{{ $stat['value'] }}</div>
                <div class="font-heading mt-1 text-xs uppercase tracking-wide text-gray-300">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- CENTRAL COMMITTEE / PAST PRESIDENTS --}}
    <section class="mx-auto max-w-7xl px-4 py-14">
        <div class="text-center">
            <h2 class="section-title-np">नेपाल मगर संघ केन्द्रीय समिति</h2>
            <p class="section-title-en mt-1">सम्मानीय अध्यक्ष देखि पूर्व अध्यक्षहरू</p>
        </div>

        <div class="swiper presidents-swiper mt-10">
            <div class="swiper-wrapper">
                @foreach ($pastPresidents as $president)
                <div class="swiper-slide">
                    <div class="card flex h-full flex-col items-center text-center">
                        <img src="{{ $president->photo_url }}" alt="{{ $president->name }}" class="h-24 w-24 rounded-full object-cover shadow glightbox-img" data-gallery="presidents">
                        <div class="np mt-3 font-bold text-navy">{{ $president->name }}</div>
                        <div class="np text-sm text-maroon">{{ $president->term_label }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('about.committee') }}" class="np inline-flex items-center gap-2 font-semibold text-maroon hover:underline">
                सबै पूर्व अध्यक्षहरू हेर्नुहोस् →
            </a>
        </div>
    </section>

    {{-- FOUR COLUMN INFO GRID --}}
    <section class="bg-navy-50 py-14">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="card flex flex-col">
                <div class="mb-3 text-3xl text-maroon"><i class="fa-solid fa-landmark"></i></div>
                <h3 class="font-heading text-lg font-bold text-navy">About Us</h3>
                <p class="mt-2 flex-1 text-sm text-gray-600">{{ Str::limit($settings->about_short_en, 110) }}</p>
                <a href="{{ route('about.index') }}" class="btn-maroon mt-4 justify-center">Read More</a>
            </div>

            <div class="card flex flex-col">
                <div class="mb-3 text-3xl text-maroon"><i class="fa-solid fa-newspaper"></i></div>
                <h3 class="font-heading text-lg font-bold text-navy">Media &amp; Information</h3>
                <ul class="mt-2 flex-1 space-y-1 text-sm text-gray-600">
                    <li><a href="{{ route('media.news.index') }}" class="hover:text-maroon">News</a></li>
                    <li><a href="{{ route('media.articles.index') }}" class="hover:text-maroon">Articles</a></li>
                    <li><a href="{{ route('media.notifications.index') }}" class="hover:text-maroon">Notifications</a></li>
                    <li><a href="{{ route('media.publications.index') }}" class="hover:text-maroon">Publications</a></li>
                    <li><a href="{{ route('media.events.index') }}" class="hover:text-maroon">Events</a></li>
                </ul>
                <a href="{{ route('media.news.index') }}" class="btn-navy-outline mt-4 justify-center !border-navy !text-navy hover:!bg-navy hover:!text-white">View All</a>
            </div>

            <div class="card flex flex-col">
                <div class="mb-3 text-3xl text-maroon"><i class="fa-solid fa-calendar-days"></i></div>
                @if($upcomingEvent)
                <div class="mb-3 inline-flex w-fit items-center rounded bg-maroon px-3 py-1 text-xs font-bold text-white">
                    {{ strtoupper($upcomingEvent->event_date->format('d M Y')) }}
                </div>
                <h3 class="font-heading text-lg font-bold text-navy">Upcoming Event</h3>
                <p class="mt-2 flex-1 text-sm font-semibold text-gray-700">{{ $upcomingEvent->title }}</p>
                <p class="text-xs text-gray-500">{{ $upcomingEvent->location }}</p>
                @endif
                <a href="{{ route('media.events.index') }}" class="btn-maroon mt-4 justify-center">View All Events</a>
            </div>

            <div class="card flex flex-col">
                <div class="mb-3 flex items-center gap-2 text-sm font-semibold">
                    <button type="button" class="gallery-tab active-tab rounded px-3 py-1" data-tab="photo">Photo</button>
                    <button type="button" class="gallery-tab rounded px-3 py-1" data-tab="video">Video</button>
                </div>
                <div class="gallery-tab-panel grid flex-1 grid-cols-3 gap-1" data-panel="photo">
                    @foreach ($photos->take(6) as $photo)
                    <a href="{{ $photo->image_url }}" class="glightbox-img" data-gallery="home-photos">
                        <img src="{{ $photo->image_url }}" alt="{{ $photo->title }}" class="h-14 w-full rounded object-cover">
                    </a>
                    @endforeach
                </div>
                <div class="gallery-tab-panel hidden grid-cols-3 gap-1" data-panel="video">
                    @foreach ($videos->take(6) as $video)
                    <a href="{{ $video->youtube_embed_url }}" class="glightbox-video">
                        <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="h-14 w-full rounded object-cover">
                    </a>
                    @endforeach
                </div>
                <a href="{{ route('gallery.photos') }}" class="btn-maroon mt-4 justify-center">View All Gallery</a>
            </div>
        </div>
    </section>

    {{-- SISTER ORGS + DONATION --}}
    <section class="mx-auto max-w-7xl px-4 py-14">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <div class="card">
                <h3 class="font-heading text-lg font-bold text-navy">Sister Organizations</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($sisterOrganizations as $org)
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <img src="{{ $org->logo_url }}" alt="{{ $org->name_en }}" class="h-8 w-8 rounded bg-white object-contain">
                        {{ $org->name_en }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('sister-organizations') }}" class="btn-navy-outline mt-6 justify-center !border-navy !text-navy hover:!bg-navy hover:!text-white">View All</a>
            </div>

            <div class="card flex flex-col items-center justify-center bg-maroon-700 text-center text-white">
                <div class="mb-3 text-4xl"><i class="fa-solid fa-hand-holding-heart"></i></div>
                <h3 class="font-heading text-lg font-bold">Lakhan Thapa Pratisthan Donation List</h3>
                <p class="mt-3 text-sm text-gray-100">
                    "Your support helps us preserve heritage, promote education and build a stronger Magar community."
                </p>
                <a href="{{ route('donation-list') }}" class="btn-navy-outline mt-6 !border-white !text-white hover:!bg-white hover:!text-maroon">View Donation List</a>
            </div>
        </div>
    </section>

</x-layouts.app>
