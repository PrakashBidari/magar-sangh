<x-layouts.app :title="$notification->title">
    <article class="mx-auto max-w-4xl px-4 py-14">
        <div class="text-xs text-gray-400">{{ optional($notification->published_at)->format('d M, Y') }}</div>
        <h1 class="mt-1 text-2xl font-bold text-navy md:text-3xl">{{ $notification->title }}</h1>
        <div class="prose prose-sm mt-8 max-w-none text-gray-700 md:prose-base">
            {!! $notification->body !!}
        </div>
        <div class="mt-10">
            <a href="{{ route('media.notifications.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to Notifications</a>
        </div>
    </article>
</x-layouts.app>
