<x-layouts.app title="Video Gallery">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">भिडियो ग्यालरी</h1>
        <p class="section-title-en mt-1">Video Gallery</p>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($videos as $video)
            <a href="{{ $video->youtube_embed_url }}" class="glightbox-video card relative overflow-hidden p-0 hover:shadow-lg">
                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="h-44 w-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/90 text-maroon shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                </div>
                <div class="absolute bottom-0 w-full bg-black/60 p-3 text-sm font-semibold text-white">{{ $video->title }}</div>
            </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $videos->links() }}
        </div>
    </section>
</x-layouts.app>
