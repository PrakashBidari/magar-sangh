<x-layouts.app title="Publications">
    <section class="mx-auto max-w-4xl px-4 py-14">
        <h1 class="section-title-np">प्रकाशनहरू</h1>
        <p class="section-title-en mt-1">Publications</p>

        <div class="mt-8 divide-y divide-gray-200 rounded-lg bg-white shadow-md">
            @foreach ($publications as $publication)
            <div class="flex flex-col items-start justify-between gap-2 px-6 py-4 sm:flex-row sm:items-center">
                <div>
                    <div class="font-medium text-navy">{{ $publication->title }}</div>
                    <div class="text-xs text-gray-400">{{ optional($publication->published_date)->format('d M, Y') }}</div>
                </div>
                <a href="{{ $publication->file_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-md bg-maroon px-4 py-2 text-xs font-semibold text-white hover:bg-maroon-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download PDF
                </a>
            </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $publications->links() }}
        </div>
    </section>
</x-layouts.app>
