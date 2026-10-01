<x-layouts.app :title="$notification->title">
    <article class="mx-auto max-w-4xl px-4 py-14">
        <div class="text-xs text-gray-400">{{ optional($notification->published_at)->format('d M, Y') }}</div>
        <h1 class="mt-1 text-2xl font-bold text-navy md:text-3xl">{{ $notification->title }}</h1>
        @if ($notification->image_url)
        <a href="{{ $notification->image_url }}" target="_blank" rel="noopener" class="mt-8 block">
            <img src="{{ $notification->image_url }}" alt="{{ $notification->title }}" class="mx-auto max-h-[80vh] w-auto max-w-full rounded-lg shadow-md">
        </a>
        @endif
        <div class="prose prose-sm mt-8 max-w-none text-gray-700 md:prose-base">
            {!! $notification->body !!}
        </div>
        <div class="mt-10">
            <a href="{{ route('media.notifications.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to Notifications</a>
        </div>
    </article>
</x-layouts.app>
