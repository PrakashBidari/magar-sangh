<x-layouts.app title="Committees">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <div class="mb-10 text-center">
            <h1 class="section-title-np">समितिहरू</h1>
            <p class="section-title-en mt-1">Committees</p>        </div>

        @if ($committees->isEmpty())
        <p class="np mt-10 text-center text-gray-500">समिति सदस्यहरूको विवरण उपलब्ध छैन।</p>
        @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($committees as $committee)
            <a href="{{ route('about.committee.show', $committee) }}" class="card group flex items-center gap-4 border-t-4 border-maroon transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-maroon-50 text-2xl">🏛️</div>
                <div class="min-w-0 flex-1">
                    <h2 class="np text-lg font-bold text-navy group-hover:text-maroon">{{ $committee->name_np }}</h2>
                    @if ($committee->name_en)
                    <p class="text-sm text-gray-500">{{ $committee->name_en }}</p>
                    @endif
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-maroon transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endforeach
        </div>
        @endif
    </section>
</x-layouts.app>
