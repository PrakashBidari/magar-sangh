<x-layouts.app :title="$article->title">
    <div class="mx-auto max-w-7xl px-4 py-14">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
            <article class="lg:col-span-8">
                <div class="text-xs text-gray-400">{{ optional($article->published_at)->format('d M, Y') }} • {{ $article->author }}</div>
                <h1 class="mt-1 text-2xl font-bold text-navy md:text-3xl">{{ $article->title }}</h1>
                <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="glightbox-img mt-6 w-full rounded-lg object-cover shadow" data-gallery="article-detail">
                <div class="prose prose-sm mt-8 max-w-none text-gray-700 md:prose-base">
                    {!! $article->body !!}
                </div>

                @if($related->isNotEmpty())
                <div class="mt-14">
                    <h2 class="font-heading text-lg font-bold text-navy">More Articles</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach ($related as $item)
                        <a href="{{ route('media.articles.show', $item) }}" class="card p-4 hover:shadow-lg">
                            <div class="text-sm font-semibold text-navy">{{ Str::limit($item->title, 60) }}</div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-10">
                    <a href="{{ route('media.articles.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to Articles</a>
                </div>
            </article>

            <aside class="lg:col-span-4">
                <x-media.recent-news :recent-news="$recentNews" />
            </aside>
        </div>
    </div>
</x-layouts.app>
