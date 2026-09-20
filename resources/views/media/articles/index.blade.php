<x-layouts.app title="Articles">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">लेखहरू</h1>
        <p class="section-title-en mt-1">Articles</p>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
            <a href="{{ route('media.articles.show', $article) }}" class="card overflow-hidden p-0 hover:shadow-lg">
                <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="h-44 w-full object-cover">
                <div class="p-5">
                    <div class="text-xs text-gray-400">{{ optional($article->published_at)->format('d M, Y') }} • {{ $article->author }}</div>
                    <h2 class="mt-1 font-bold text-navy">{{ $article->title }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $article->excerpt }}</p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $articles->links() }}
        </div>
    </section>
</x-layouts.app>
