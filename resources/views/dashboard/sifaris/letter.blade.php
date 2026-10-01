@php
    $backUrl = auth()->user()->can('sifaris.view') && $sifaris->user_id !== auth()->id()
        ? route('dashboard.sifaris.show', $sifaris)
        : route('dashboard.my-sifaris.index');
    $file = Str::lower($sifaris->reference()).'-sifaris';
@endphp
<x-layouts.dashboard title="Sifaris Letter">
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-navy">📜 Sifaris Letter</h2>
                <p class="np text-sm text-gray-500">{{ $sifaris->applicant_name }} · {{ $sifaris->reference() }}</p>
            </div>
            <a href="{{ $backUrl }}" class="text-sm font-semibold text-maroon hover:underline">← Back</a>
        </div>

        @if ($sifaris->isApproved())
        <div class="card mb-5 flex flex-wrap items-center gap-2">
            <button type="button" id="dl-pdf" class="btn-maroon justify-center !px-5 !py-2.5 text-sm">⬇ Download PDF</button>
            <button type="button" id="dl-png" class="rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-navy hover:bg-gray-50">Download PNG</button>
            <span id="dl-status" class="ml-auto hidden text-xs font-semibold text-gray-500" role="status"></span>
        </div>
        @else
        <div class="mb-5 rounded-md bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">Preview only. The letter can be downloaded after the request is approved.</div>
        @endif

        <div class="rounded-lg bg-gradient-to-br from-navy-50 to-white p-3 sm:p-8">
            @include('dashboard.sifaris._letter')
        </div>
    </div>

    @if ($sifaris->isApproved())
    @push('scripts')
    {{-- html-to-image lets the browser lay out the letter itself, so the file matches what is on screen --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        (function () {
            const name = @json($file);
            const status = document.getElementById('dl-status');
            const buttons = document.querySelectorAll('#dl-pdf, #dl-png');

            const busy = (text) => {
                buttons.forEach((b) => (b.disabled = !!text));
                status.textContent = text || '';
                status.classList.toggle('hidden', !text);
            };

            const BLANK = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

            // The letter node is 1024 x 1536 px (unscaled); 2x gives a 2048 x 3072 image, sharp enough to print on A4.
            const capture = async (format = 'png') => {
                await document.fonts.ready;
                const node = document.getElementById('sifaris-letter');
                const options = { cacheBust: true, imagePlaceholder: BLANK, fontEmbedCSS: await window.htmlToImage.getFontEmbedCSS(node) };
                await window.htmlToImage.toPng(node, { ...options, pixelRatio: 1 }); // warm-up: some browsers load fonts/images lazily on the first pass
                return format === 'jpeg'
                    ? window.htmlToImage.toJpeg(node, { ...options, pixelRatio: 2, quality: 0.92, backgroundColor: '#ffffff' })
                    : window.htmlToImage.toPng(node, { ...options, pixelRatio: 2 });
            };

            const save = (blob, filename) => {
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(link.href), 2000);
            };

            const run = async (text, task) => {
                busy(text);
                try {
                    await task();
                } catch (error) {
                    console.error(error);
                    alert('Could not create the file. Please check your internet connection and try again.');
                } finally {
                    busy('');
                }
            };

            document.getElementById('dl-png').addEventListener('click', () => run('Preparing image…', async () => {
                const dataUrl = await capture();
                save(await (await fetch(dataUrl)).blob(), name + '.png');
            }));

            // A4 page; the 2:3 letter fills the full height and is centred across.
            document.getElementById('dl-pdf').addEventListener('click', () => run('Preparing PDF…', async () => {
                const dataUrl = await capture('jpeg');
                const pdf = new window.jspdf.jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                const height = 297, width = height * 1024 / 1536;
                pdf.addImage(dataUrl, 'JPEG', (210 - width) / 2, 0, width, height);
                pdf.save(name + '.pdf');
            }));
        })();
    </script>
    @endpush
    @endif
</x-layouts.dashboard>
