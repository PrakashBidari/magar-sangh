{{--
    The sifaris letter: the applicant's details written onto public/images/sifaris-template.jpg.

    The letter is laid out at the template's own size (1024 x 1536 px) and scaled down on screen,
    so the positions below are template pixels (measured from the dotted lines). If the template
    image is ever replaced, keep the same layout and size or update these numbers.

    Fields: [left, width, baseline y, value]. Text is shrunk to fit its dotted line.

    The signature, signatory and footer contact details come from Dashboard > Sifaris > Sifaris Settings.
    Without a request ($sifaris = null) only those are drawn: the blank letter used as a sample.
--}}
@php
    $letter = \App\Models\SifarisSetting::current();
    $fields = ! $sifaris ? [] : [
        [180, 160, 459, $sifaris->letter_number],
        [180, 160, 496, $sifaris->dispatch_number],
        [806, 172, 461, $sifaris->letter_date],
        [303, 130, 863, $sifaris->province],
        [588, 162, 863, $sifaris->district],
        [840, 172, 863, $sifaris->municipality],
        [276, 136, 929, $sifaris->ward_no],
        [545, 205, 929, $sifaris->grandparent_name],
        [86, 213, 997, $sifaris->parent_name],
        [566, 186, 997, $sifaris->applicant_name],
    ];
    // The template prints every option ("…/…/…"); these cover it with the one that applies: [left, top, width, height, text].
    $relations = ! $sifaris ? [] : [
        [794, 899, 198, 40, $sifaris->grandparent_relation],
        [344, 967, 216, 40, $sifaris->parent_relation],
    ];
@endphp
@once
<style>
    .sifaris-frame { position: relative; width: 100%; max-width: 640px; margin: 0 auto; aspect-ratio: 1024 / 1536; overflow: hidden; border-radius: 6px; box-shadow: 0 18px 40px -12px rgba(0, 31, 91, .35), 0 4px 10px rgba(0, 0, 0, .1); }
    .sifaris-scale { position: absolute; left: 0; top: 0; width: 1024px; height: 1536px; transform-origin: 0 0; }
    .sifaris-letter { position: relative; width: 1024px; height: 1536px; overflow: hidden; background: #fff; font-family: 'Mukta', 'Noto Sans Devanagari', sans-serif; }
    .sifaris-bg { position: absolute; inset: 0; width: 100%; height: 100%; }
    .sifaris-field { position: absolute; height: 36px; display: flex; align-items: flex-end; justify-content: center; white-space: nowrap; overflow: hidden; }
    .sifaris-field span { font-size: 25px; font-weight: 700; line-height: 1.1; color: #0b2a7a; }
    .sifaris-relation { position: absolute; display: flex; align-items: center; padding-left: 6px; background: #fafdfd; font-size: 27px; font-weight: 500; color: #1a1a1a; }
    .sifaris-photo-box { position: absolute; left: 739px; top: 504px; width: 227px; height: 234px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #fff; }
    .sifaris-photo { width: 174px; height: 224px; object-fit: cover; border: 1px solid #cbd5e1; }
    .sifaris-signature { position: absolute; left: 748px; top: 1098px; width: 226px; height: 127px; object-fit: contain; object-position: center bottom; mix-blend-mode: multiply; }
    .sifaris-signatory { position: absolute; left: 712px; width: 300px; display: flex; align-items: center; justify-content: center; white-space: nowrap; overflow: hidden; font-weight: 700; color: #0b2a7a; }
    .sifaris-contact { position: absolute; display: flex; align-items: center; overflow: hidden; font-size: 17px; font-weight: 500; line-height: 25px; letter-spacing: .02em; color: #fff; }
    .sifaris-contact span { white-space: nowrap; }
    .sifaris-stamp { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) rotate(-24deg); padding: 10px 36px; border: 8px solid #dc2626; border-radius: 14px; color: #dc2626; font-family: sans-serif; font-size: 72px; font-weight: 900; letter-spacing: .08em; background: rgba(255, 255, 255, .6); white-space: nowrap; }
    .sifaris-frame[data-letter-zoom] { cursor: zoom-in; }
    .sifaris-zoom { width: 100%; height: 100%; max-width: none; max-height: none; margin: 0; padding: 56px 16px 24px; border: 0; background: transparent; overflow: auto; }
    .sifaris-zoom::backdrop { background: rgba(0, 0, 0, .88); }
    .sifaris-zoom .sifaris-frame { max-width: 1024px; }
    .sifaris-zoom-close { position: fixed; right: 16px; top: 12px; z-index: 1; width: 36px; height: 36px; border-radius: 9999px; background: rgba(255, 255, 255, .15); color: #fff; font-size: 18px; line-height: 36px; text-align: center; }
    .sifaris-zoom-close:hover { background: rgba(255, 255, 255, .3); }
</style>
@endonce
<div class="sifaris-frame" data-letter-frame data-letter-zoom title="Click to zoom">
    <div class="sifaris-scale">
        <div class="sifaris-letter" id="sifaris-letter">
            <img src="{{ asset('images/sifaris-template.jpg') }}?v=3" alt="" class="sifaris-bg">

            @foreach ($fields as [$left, $width, $baseline, $value])
            <div class="sifaris-field" style="left:{{ $left }}px;top:{{ $baseline - 40 }}px;width:{{ $width }}px"><span data-fit>{{ $value }}</span></div>
            @endforeach

            @foreach ($relations as [$left, $top, $width, $height, $text])
            <div class="sifaris-relation" style="left:{{ $left }}px;top:{{ $top }}px;width:{{ $width }}px;height:{{ $height }}px">{{ $text }}</div>
            @endforeach

            {{-- Signature image, then the signatory's name and position --}}
            @if ($letter->signature_url)
            <img src="{{ $letter->signature_url }}" alt="" class="sifaris-signature">
            @endif
            <div class="sifaris-signatory" style="top:1228px;height:40px;font-size:30px"><span data-fit>{{ $letter->signatory_name }}</span></div>
            @if ($letter->signatory_title)
            <div class="sifaris-signatory" style="top:1267px;height:38px;font-size:26px"><span data-fit>({{ $letter->signatory_title }})</span></div>
            @endif

            {{-- Footer: telephone (wraps onto two lines), e-mail and website, next to the template's icons --}}
            @if ($letter->phoneNumbers())
            <div class="sifaris-contact" style="left:107px;top:1436px;width:240px;height:64px;align-items:flex-start;padding-top:7px">
                <span style="margin-right:6px">Tel:</span>
                <div>@foreach ($letter->phoneNumbers() as $number)<span>{{ $number }}@if (! $loop->last),@endif</span> @endforeach</div>
            </div>
            @endif
            @if ($letter->email)
            <div class="sifaris-contact" style="left:428px;top:1444px;width:302px;height:34px"><span data-fit>E-mail: {{ $letter->email }}</span></div>
            @endif
            @if ($letter->website)
            <div class="sifaris-contact" style="left:818px;top:1446px;width:192px;height:34px"><span data-fit>web: {{ $letter->website }}</span></div>
            @endif

            @if ($sifaris)
            {{-- Passport-size photo (35 x 45 ratio) inside the template's photo box --}}
            <div class="sifaris-photo-box">
                <img src="{{ $sifaris->photo_url }}" alt="" class="sifaris-photo">
            </div>

            @unless ($sifaris->isApproved())
            <div class="sifaris-stamp">{{ $sifaris->isRejected() ? 'DISAPPROVED' : 'NOT APPROVED' }}</div>
            @endunless
            @endif
        </div>
    </div>
</div>

@once
@push('scripts')
{{-- Lightbox: clicking a letter opens a copy of it at full size, to check every filled-in value --}}
<dialog id="sifaris-zoom" class="sifaris-zoom">
    <button type="button" class="sifaris-zoom-close" id="sifaris-zoom-close" aria-label="Close">✕</button>
    <div class="sifaris-frame" data-letter-frame><div class="sifaris-scale"></div></div>
</dialog>
<script>
    (function () {
        // Scale each 1024px letter to its frame, and shrink long values to fit their dotted line.
        const scale = () => document.querySelectorAll('[data-letter-frame]').forEach((frame) => {
            frame.querySelector('.sifaris-scale').style.transform = 'scale(' + frame.clientWidth / 1024 + ')';
        });
        const fit = () => document.querySelectorAll('.sifaris-letter [data-fit]').forEach((span) => {
            let size = parseFloat(getComputedStyle(span).fontSize);
            while (span.offsetWidth > span.parentElement.clientWidth && size > 12) {
                span.style.fontSize = --size + 'px';
            }
        });
        scale();
        window.addEventListener('resize', scale);
        document.fonts.ready.then(fit);

        const zoom = document.getElementById('sifaris-zoom');
        document.addEventListener('click', (event) => {
            const frame = event.target.closest('[data-letter-zoom]');
            if (!frame) return;
            const copy = frame.querySelector('.sifaris-letter').cloneNode(true);
            copy.removeAttribute('id'); // the download script finds the original by id
            zoom.querySelector('.sifaris-scale').replaceChildren(copy);
            zoom.showModal();
            zoom.scrollTop = 0;
            scale();
        });
        document.getElementById('sifaris-zoom-close').addEventListener('click', () => zoom.close());
        zoom.addEventListener('click', (event) => { if (event.target === zoom) zoom.close(); });
    })();
</script>
@endpush
@endonce
