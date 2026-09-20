<x-layouts.app :title="$event->title">
    <article class="mx-auto max-w-4xl px-4 py-14">
        <div class="inline-flex items-center rounded bg-maroon px-3 py-1 text-xs font-bold text-white">
            {{ strtoupper($event->event_date->format('d M Y')) }}
        </div>
        <h1 class="mt-3 text-2xl font-bold text-navy md:text-3xl">{{ $event->title }}</h1>
        <p class="mt-1 text-sm text-gray-500">📍 {{ $event->location }} @if($event->event_time) • {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }} @endif</p>
        <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="glightbox-img mt-6 w-full rounded-lg object-cover shadow" data-gallery="event-detail">
        <div class="prose prose-sm mt-8 max-w-none text-gray-700 md:prose-base">
            {!! $event->description !!}
        </div>
        <div class="mt-10">
            <a href="{{ route('media.events.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to Events</a>
        </div>
    </article>
</x-layouts.app>
