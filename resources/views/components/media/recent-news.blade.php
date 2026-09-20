@props(['recentNews'])

<div class="card">
    <h2 class="font-heading text-lg font-bold text-navy">Recent News</h2>
    <ul class="mt-4 space-y-4">
        @foreach ($recentNews as $item)
        <li>
            <a href="{{ route('media.news.show', $item) }}" class="group flex items-center gap-3">
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="h-14 w-14 shrink-0 rounded object-cover">
                <span class="flex-1 text-sm font-semibold text-gray-700 group-hover:text-maroon">{{ Str::limit($item->title, 55) }}</span>
                <i class="fa-solid fa-chevron-right shrink-0 text-xs text-gray-400 transition group-hover:translate-x-1 group-hover:text-maroon"></i>
            </a>
        </li>
        @endforeach
    </ul>
</div>
