<x-layouts.app title="Donation List">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">दान सूची</h1>
        <p class="section-title-en mt-1">Donation List – Lakhan Thapa Pratisthan</p>

        {{-- Content managed from Dashboard > Lakhan Thapa Pratisthan > Settings --}}
        @if (filled(strip_tags($siteSettings->donation_page_content ?? '', '<img><a><iframe><video>')))
        <div id="donation-page-content" class="prose mt-8 max-w-none rounded-lg bg-white p-6 shadow-md">
            {!! $siteSettings->donation_page_content !!}
        </div>
        @endif

        {{-- PDF files from Dashboard > Lakhan Thapa Pratisthan > Settings; Download opens the PDF in a new tab --}}
        @if ($documents->isNotEmpty())
        <div class="mt-8 divide-y divide-gray-100 rounded-lg bg-white shadow-md">
            @foreach ($documents as $document)
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-xs font-extrabold text-red-600">PDF</span>
                    <span class="font-semibold text-navy">{{ $document->title }}</span>
                </div>
                <a href="{{ $document->file_url }}" target="_blank" rel="noopener" class="btn-maroon justify-center !px-5 !py-2 text-sm">⬇ Download</a>
            </div>
            @endforeach
        </div>
        @endif

        <div class="mt-10">
            <livewire:donation-list-page />
        </div>
    </section>

    @if (filled($siteSettings->donation_page_content))
    @push('scripts')
    <script>
        // Show a preview under every PDF link so visitors can read it without downloading.
        document.querySelectorAll('#donation-page-content a[href$=".pdf" i]').forEach((link) => {
            const frame = document.createElement('iframe');
            frame.src = link.href;
            frame.title = link.textContent.trim() || 'PDF document';
            frame.className = 'not-prose mt-2 h-[600px] w-full rounded-md border border-gray-200';
            (link.closest('p') || link).after(frame);
        });
    </script>
    @endpush
    @endif
</x-layouts.app>
