{{--
    Notification popups on the public website (set per notification in Dashboard > Notifications).

    A popup opens:
      - when the page loads: every time ("always"), or only the first time the visitor ever sees it;
      - when the visitor is about to leave (mouse moves out through the top of the window), once per browser session;
      - again every N minutes while the visitor stays on the website.
    What was shown and when is remembered in the visitor's browser (localStorage / sessionStorage).
--}}
@php
    $popups = \App\Models\NotificationItem::popupPayload(request()->routeIs('home'));
@endphp
@if ($popups->isNotEmpty())
<dialog id="notice-popup" class="notice-popup" aria-labelledby="notice-popup-title">
    <div class="notice-popup-card">
        <button type="button" class="notice-popup-close" data-popup-close aria-label="Close">✕</button>
        <a data-popup-link class="notice-popup-image"><img data-popup-image alt=""></a>
        <div class="notice-popup-body">
            <div class="notice-popup-label np">सूचना · Notice</div>
            <h2 id="notice-popup-title" data-popup-title class="notice-popup-title"></h2>
            <p data-popup-text class="notice-popup-text"></p>
            <div class="notice-popup-actions">
                <button type="button" data-popup-close class="notice-popup-btn notice-popup-btn-plain">Close</button>
                <a data-popup-link class="notice-popup-btn notice-popup-btn-main">Read more →</a>
            </div>
        </div>
    </div>
</dialog>
<style>
    .notice-popup { width: calc(100% - 24px); max-width: 560px; max-height: calc(100vh - 24px); margin: auto; padding: 0; border: 0; border-radius: 14px; background: transparent; overflow: visible; }
    .notice-popup::backdrop { background: rgba(0, 20, 60, .72); }
    .notice-popup[open] { animation: notice-popup-in .25s ease-out; }
    @keyframes notice-popup-in { from { opacity: 0; transform: translateY(14px) scale(.97); } to { opacity: 1; transform: none; } }
    .notice-popup-card { position: relative; display: flex; flex-direction: column; max-height: calc(100vh - 24px); overflow: hidden; border-radius: 14px; background: #fff; box-shadow: 0 25px 60px rgba(0, 0, 0, .45); }
    .notice-popup-close { position: absolute; right: 10px; top: 10px; z-index: 2; width: 34px; height: 34px; border-radius: 9999px; background: rgba(0, 0, 0, .6); color: #fff; font-size: 16px; line-height: 34px; text-align: center; cursor: pointer; }
    .notice-popup-close:hover { background: #8B0000; }
    .notice-popup-image { display: block; min-height: 0; flex: 1 1 auto; background: #f1f5f9; }
    .notice-popup-image[hidden] { display: none; }
    .notice-popup-image img { display: block; width: 100%; max-height: 62vh; object-fit: contain; }
    .notice-popup-body { flex-shrink: 0; padding: 16px 20px 18px; }
    .notice-popup-label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #8B0000; }
    .notice-popup-title { margin-top: 2px; padding-right: 34px; font-size: 18px; font-weight: 700; line-height: 1.35; color: #001f5b; }
    .notice-popup-image:not([hidden]) + .notice-popup-body .notice-popup-title { padding-right: 0; }
    .notice-popup-text { margin-top: 6px; font-size: 14px; line-height: 1.6; color: #4b5563; }
    .notice-popup-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px; }
    .notice-popup-btn { padding: 8px 18px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; }
    .notice-popup-btn-plain { border: 1px solid #d1d5db; color: #4b5563; }
    .notice-popup-btn-plain:hover { background: #f9fafb; }
    .notice-popup-btn-main { background: #8B0000; color: #fff; }
    .notice-popup-btn-main:hover { background: #6d0000; }
</style>
<script>
    (function () {
        const popups = @json($popups);
        const dialog = document.getElementById('notice-popup');
        if (!dialog || typeof dialog.showModal !== 'function') return;

        // Browsers in private mode may refuse storage; the popup then simply behaves as "every time".
        const store = (area) => ({
            get: (key) => { try { return window[area].getItem(key); } catch (e) { return null; } },
            set: (key, value) => { try { window[area].setItem(key, value); } catch (e) {} },
        });
        const local = store('localStorage');
        const session = store('sessionStorage');
        const shownKey = (p) => 'notice-popup-shown-' + p.id; // time it was last shown, in ms
        const exitKey = (p) => 'notice-popup-exit-' + p.id;

        const queue = [];
        const image = dialog.querySelector('[data-popup-image]');

        const open = (p) => {
            if (dialog.open) {
                if (!queue.includes(p)) queue.push(p); // shown one after another
                return;
            }
            dialog.querySelector('[data-popup-title]').textContent = p.title;
            const text = dialog.querySelector('[data-popup-text]');
            text.textContent = p.text;
            text.hidden = !p.text;
            image.parentElement.hidden = !p.image;
            if (p.image) image.src = p.image; else image.removeAttribute('src');
            dialog.querySelectorAll('[data-popup-link]').forEach((a) => (a.href = p.url));
            dialog.showModal();
            local.set(shownKey(p), String(Date.now()));
            again(p);
        };

        // "Every N minutes": counted from the last time it was shown, also across page changes.
        const timers = {};
        const again = (p) => {
            if (!p.repeat) return;
            clearTimeout(timers[p.id]);
            const last = Number(local.get(shownKey(p))) || Date.now();
            timers[p.id] = setTimeout(() => open(p), Math.max(0, last + p.repeat * 60000 - Date.now()));
        };

        dialog.querySelectorAll('[data-popup-close]').forEach((b) => b.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
        dialog.addEventListener('close', () => { if (queue.length) open(queue.shift()); });

        popups.forEach((p) => {
            if (p.always || !local.get(shownKey(p))) open(p);
            else again(p);
        });

        // Exit intent: the mouse leaves through the top of the window (towards the tabs / address bar).
        const leaving = popups.filter((p) => p.exit);
        if (leaving.length) {
            document.documentElement.addEventListener('mouseleave', (event) => {
                if (event.clientY > 0) return;
                leaving.filter((p) => !session.get(exitKey(p))).forEach((p) => {
                    session.set(exitKey(p), '1');
                    open(p);
                });
            });
        }
    })();
</script>
@endif
