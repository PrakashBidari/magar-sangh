<x-layouts.app title="News">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">समाचार</h1>
        <p class="section-title-en mt-1">News</p>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($newsItems as $item)
            <a href="{{ route('media.news.show', $item) }}" class="card overflow-hidden p-0 hover:shadow-lg">
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="h-44 w-full object-cover">
                <div class="p-5">
                    <div class="text-xs text-gray-400">{{ optional($item->published_at)->format('d M, Y') }}</div>
                    <h2 class="mt-1 font-bold text-navy">{{ $item->title }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $item->excerpt }}</p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $newsItems->links() }}
        </div>
    </section>
</x-layouts.app>
