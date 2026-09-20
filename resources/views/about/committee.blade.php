<x-layouts.app title="Central Committee">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">केन्द्रीय समिति</h1>
        <p class="section-title-en mt-1">Central Committee</p>

        <div class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($current as $member)
            <div class="card text-center">
                <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="mx-auto h-20 w-20 rounded-full object-cover shadow glightbox-img" data-gallery="committee">
                <div class="np mt-2 text-sm font-bold text-navy">{{ $member->name }}</div>
                <div class="np text-xs text-maroon">{{ $member->position_np }}</div>
            </div>
            @endforeach
        </div>

        <div class="mt-16 text-center">
            <h2 class="section-title-np">पूर्व अध्यक्षहरू</h2>
            <p class="section-title-en mt-1">Former Presidents</p>
        </div>

        <div class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7">
            @foreach ($pastPresidents as $president)
            <div class="card text-center">
                <img src="{{ $president->photo_url }}" alt="{{ $president->name }}" class="mx-auto h-20 w-20 rounded-full object-cover shadow glightbox-img" data-gallery="past-presidents">
                <div class="np mt-2 text-sm font-bold text-navy">{{ $president->name }}</div>
                <div class="np text-xs text-maroon">{{ $president->term_label }}</div>
            </div>
            @endforeach
        </div>
    </section>
</x-layouts.app>
