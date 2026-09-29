<x-layouts.app title="Sister Organizations">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">सहयोगी संस्थाहरू</h1>
        <p class="section-title-en mt-1">Sister Organizations</p>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($organizations as $org)
            <div class="card flex flex-col items-center text-center">
                <img src="{{ $org->logo_url }}" alt="{{ $org->name_en }}" class="h-20 w-20 rounded-md bg-white object-contain p-1 shadow">
                <h2 class="mt-4 font-bold text-navy">{{ $org->name_en }}</h2>
                <p class="np text-sm text-maroon">{{ $org->name_np }}</p>
                <p class="mt-2 flex-1 text-sm text-gray-600">{{ $org->blurb }}</p>
                @if($org->link)
                <a href="{{ $org->link }}" target="_blank" rel="noopener" class="mt-4 text-sm font-semibold text-maroon hover:underline">Visit Website →</a>
                @endif
            </div>
            @endforeach
        </div>
    </section>
</x-layouts.app>
