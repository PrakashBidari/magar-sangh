<x-layouts.app title="Notifications">
    <section class="mx-auto max-w-4xl px-4 py-14">
        <h1 class="section-title-np">सूचनाहरू</h1>
        <p class="section-title-en mt-1">Notifications</p>

        <div class="mt-8 divide-y divide-gray-200 rounded-lg bg-white shadow-md">
            @foreach ($notifications as $notification)
            <a href="{{ route('media.notifications.show', $notification) }}" class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50">
                <span class="font-medium text-navy">{{ $notification->title }}</span>
                <span class="shrink-0 text-xs text-gray-400">{{ optional($notification->published_at)->format('d M, Y') }}</span>
            </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $notifications->links() }}
        </div>
    </section>
</x-layouts.app>
