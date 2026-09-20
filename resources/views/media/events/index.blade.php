<x-layouts.app title="Events">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">कार्यक्रमहरू</h1>
        <p class="section-title-en mt-1">Events</p>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)
            <a href="{{ route('media.events.show', $event) }}" class="card overflow-hidden p-0 hover:shadow-lg">
                <div class="relative">
                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="h-44 w-full object-cover">
                    <div class="absolute left-3 top-3 rounded bg-maroon px-3 py-1 text-xs font-bold text-white">
                        {{ strtoupper($event->event_date->format('d M Y')) }}
                    </div>
                </div>
                <div class="p-5">
                    <h2 class="font-bold text-navy">{{ $event->title }}</h2>
                    <p class="mt-2 text-sm text-gray-600">📍 {{ $event->location }}</p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $events->links() }}
        </div>
    </section>
</x-layouts.app>
