@php
    $backUrl = auth()->user()->hasRole('admin') && $membership->user_id !== auth()->id()
        ? route('dashboard.membership.show', $membership)
        : route('dashboard.my-membership.show');
    // Municipality / Rural municipality-Ward, District, Province
    $address = $membership->municipality.'-'.$membership->ward_no.', '.$membership->district.', '.$membership->province;
    $validTill = $membership->expires_at?->format('d M Y') ?? 'Lifetime';
    $file = Str::lower($membership->membership_number);
@endphp
<x-layouts.dashboard title="ID Card">
    <style>
        /* The card is drawn at a fixed 324 x 514 px (CR80 ratio, 54 x 85.6 mm) so the exported image is always identical. */
        .id-card { position: relative; display: flex; flex-direction: column; width: 324px; height: 514px; overflow: hidden; border-radius: 16px; background: #fff; color: #1f2937; font-family: 'Noto Sans', 'Noto Sans Devanagari', sans-serif; }
        .id-card * { box-sizing: border-box; }
        .id-shadow { border-radius: 16px; box-shadow: 0 18px 40px -12px rgba(0, 31, 91, .45), 0 4px 10px rgba(0, 0, 0, .12); }
        .id-head { position: relative; flex-shrink: 0; height: 112px; padding: 10px 16px 0; background: linear-gradient(135deg, #8B0000 0%, #5c0000 60%, #001F5B 140%); color: #fff; }
        .id-head::after { content: ''; position: absolute; right: -40px; top: -40px; width: 150px; height: 150px; border-radius: 50%; background: rgba(212, 175, 55, .16); }
        .id-head::before { content: ''; position: absolute; left: -30px; bottom: -60px; width: 130px; height: 130px; border-radius: 50%; background: rgba(255, 255, 255, .07); }
        .id-gold { flex-shrink: 0; height: 5px; background: linear-gradient(90deg, #D4AF37, #f2dc8a, #D4AF37); }
        .id-org { min-width: 0; font-family: 'Yatra One', 'Noto Sans Devanagari', serif; font-size: 30px; font-weight: 400; line-height: 1.15; letter-spacing: .01em; color: #fff; text-shadow: 0 2px 6px rgba(0, 0, 0, .35); white-space: nowrap; }
        .id-logo { width: 44px; height: 52px; flex-shrink: 0; padding: 3px; border-radius: 8px; background: #fff center / contain no-repeat; background-origin: content-box; border: 2px solid #D4AF37; }
        .id-photo { flex-shrink: 0; width: 96px; height: 96px; margin: -32px auto 0; position: relative; z-index: 2; border-radius: 50%; border: 4px solid #fff; outline: 2px solid #D4AF37; background: #e5e7eb center / cover no-repeat; box-shadow: 0 6px 14px rgba(0, 0, 0, .25); }
        .id-label { font-size: 8.5px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #9ca3af; }
        .id-value { margin-top: 1px; font-size: 11.5px; font-weight: 600; color: #111827; line-height: 1.3; word-break: break-word; }
        .id-grid { display: grid; grid-template-columns: 0.85fr 1.15fr; gap: 7px 14px; text-align: left; }
        .id-foot { flex-shrink: 0; padding: 8px 12px; background: #001F5B; color: #fff; font-size: 9.5px; line-height: 1.45; text-align: center; border-top: 3px solid #D4AF37; }
        .id-watermark { position: absolute; left: 50%; top: 58%; width: 260px; height: 300px; transform: translate(-50%, -50%); opacity: .06; background: center / contain no-repeat; }
        .id-stamp { position: absolute; left: 50%; top: 52%; z-index: 5; transform: translate(-50%, -50%) rotate(-24deg); padding: 6px 20px; border: 4px solid #dc2626; border-radius: 8px; color: #dc2626; font-size: 34px; font-weight: 900; letter-spacing: .1em; background: rgba(255, 255, 255, .55); }
    </style>

    {{-- Display font for the association name on the card (only loaded on this page) --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Yatra+One&display=swap">

    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-navy">🎫 Membership ID Card</h2>
                <p class="text-sm text-gray-500">{{ $membership->displayName() }} · {{ $membership->membership_number }}</p>
            </div>
            <a href="{{ $backUrl }}" class="text-sm font-semibold text-maroon hover:underline">← Back</a>
        </div>

        {{-- Actions --}}
        <div class="card mb-5 flex flex-wrap items-center gap-2">
            <button type="button" id="dl-pdf" class="btn-maroon justify-center !px-5 !py-2.5 text-sm">⬇ Download PDF</button>
            <button type="button" id="dl-png" class="rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-navy hover:bg-gray-50">Download PNG</button>
            <span id="dl-status" class="ml-auto hidden text-xs font-semibold text-gray-500" role="status"></span>
        </div>

        @if ($membership->isExpired())
        <div class="mb-5 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">This membership expired on {{ $membership->expires_at->format('d M, Y') }}. The card is marked as expired.</div>
        @endif

        <div class="id-print flex justify-center rounded-lg bg-gradient-to-br from-navy-50 to-white p-4 sm:p-8">
            <div class="id-sheet">
                <div class="id-shadow">
                    <div class="id-card" id="id-card">
                        @if ($siteSettings->logo_url)<div class="id-watermark" style="background-image:url('{{ $siteSettings->logo_url }}')"></div>@endif

                        <div class="id-head">
                            <div class="relative z-10 flex items-center justify-center gap-3">
                                @if ($siteSettings->logo_url)<div class="id-logo" style="background-image:url('{{ $siteSettings->logo_url }}')"></div>@endif
                                <div class="id-org np">{{ $siteSettings->site_name_np }}</div>
                            </div>
                            <div class="relative z-10" style="margin-top:3px;text-align:center;font-size:9.5px;line-height:1.2;font-weight:800;letter-spacing:.32em;color:#f2dc8a">MEMBERSHIP CARD</div>
                        </div>
                        <div class="id-gold"></div>

                        <div class="id-photo" style="background-image:url('{{ $membership->photo_url }}')"></div>

                        {{-- space-evenly: every gap (photo > name > details > signatures > validity > footer) is the same height --}}
                        <div style="position:relative;z-index:1;flex:1;display:flex;flex-direction:column;justify-content:space-evenly;padding:0 18px">
                            <div style="text-align:center;font-size:18px;font-weight:800;line-height:1.2;color:#001F5B;word-break:break-word">{{ $membership->displayName() }}</div>

                            <div class="id-grid" style="padding-top:8px;border-top:1.5px solid #D4AF37">
                                <div><div class="id-label">Phone</div><div class="id-value">{{ $membership->mobile }}</div></div>
                                <div><div class="id-label">Email</div><div class="id-value" style="word-break:break-all">{{ $membership->email }}</div></div>
                                <div><div class="id-label">Membership</div><div class="id-value">{{ $membership->type->name_en }} · {{ $membership->type->durationLabel() }}</div></div>
                                <div><div class="id-label">Membership No.</div><div class="id-value" style="font-family:ui-monospace,Menlo,Consolas,monospace;font-weight:800;color:#8B0000">{{ $membership->membership_number }}</div></div>
                                <div style="grid-column:1 / -1"><div class="id-label">Address</div><div class="id-value">{{ $address }}</div></div>
                            </div>

                            {{-- Signatures: the holder's (only when uploaded) and the association's authorized signatory --}}
                            <div style="display:flex;justify-content:center;gap:18px;text-align:center">
                                @if ($membership->signature_url)
                                <div style="flex:1">
                                    <div style="height:30px;background:center bottom / contain no-repeat;background-image:url('{{ $membership->signature_url }}')"></div>
                                    <div style="border-top:1px solid #6b7280;padding-top:2px;white-space:nowrap" class="id-label">Holder's signature</div>
                                </div>
                                @endif
                                <div style="{{ $membership->signature_url ? 'flex:1' : 'width:150px' }}">
                                    <div style="height:30px"></div>
                                    <div style="border-top:1px solid #6b7280;padding-top:2px;white-space:nowrap" class="id-label">Authorized signature</div>
                                </div>
                            </div>

                            <div style="display:flex;padding:6px 0;border:1.5px solid #D4AF37;border-radius:10px;background:#fbf6e7;text-align:center">
                                <div style="flex:1;border-right:1px solid #e5d9a8">
                                    <div class="id-label">Member since</div>
                                    <div class="id-value">{{ $membership->approved_at->format('d M Y') }}</div>
                                </div>
                                <div style="flex:1">
                                    <div class="id-label">Valid till</div>
                                    <div class="id-value" style="{{ $membership->isExpired() ? 'color:#dc2626' : '' }}">{{ $validTill }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="id-foot">
                            <div style="font-weight:700">{{ $siteSettings->phone }} · {{ $siteSettings->email }}</div>
                        </div>
                        @if ($membership->isExpired())<div class="id-stamp">EXPIRED</div>@endif
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-4 text-center text-xs text-gray-400">Download the PDF or PNG to print on a card printer (CR80, 54 × 85.6 mm).</p>
    </div>

    @push('scripts')
    {{-- html-to-image lets the browser lay out the card itself, so the file matches what is on screen (html2canvas shifted text) --}}
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

            // Returns a data URL rendered at 4x so the file is print quality (about 1300 x 2050 px).
            // PNG keeps the rounded corners transparent; JPEG (for the PDF) is far smaller than a PNG.
            const capture = async (format = 'png') => {
                await document.fonts.load('30px "Yatra One"', @json($siteSettings->site_name_np)).catch(() => {});
                await document.fonts.ready;
                const node = document.getElementById('id-card');
                const options = { cacheBust: true, imagePlaceholder: BLANK, fontEmbedCSS: await window.htmlToImage.getFontEmbedCSS(node) };
                await window.htmlToImage.toPng(node, { ...options, pixelRatio: 1 }); // warm-up: some browsers load fonts/images lazily on the first pass
                return format === 'jpeg'
                    ? window.htmlToImage.toJpeg(node, { ...options, pixelRatio: 4, quality: 0.95, backgroundColor: '#ffffff' })
                    : window.htmlToImage.toPng(node, { ...options, pixelRatio: 4 });
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
                save(await (await fetch(dataUrl)).blob(), name + '-id-card.png');
            }));

            document.getElementById('dl-pdf').addEventListener('click', () => run('Preparing PDF…', async () => {
                const dataUrl = await capture('jpeg');
                const pdf = new window.jspdf.jsPDF({ orientation: 'portrait', unit: 'mm', format: [54, 85.6] });
                pdf.addImage(dataUrl, 'JPEG', 0, 0, 54, 85.6);
                pdf.save(name + '-id-card.pdf');
            }));
        })();
    </script>
    @endpush
</x-layouts.dashboard>
