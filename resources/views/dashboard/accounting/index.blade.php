@php
    $type = $filters['type'];
    $tabs = ['' => 'All', 'income' => 'Income', 'expense' => 'Expense'];
    $balance = $income - $expense;
    $hasFilters = $filters['from'] || $filters['to'] || $filters['q'] || $filters['categories'] || $filters['methods'];
    $title = match ($type) { 'income' => 'Income', 'expense' => 'Expense', default => 'Income & Expense' };
    $input = 'mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon';
    // What the PDF header says about the active filter, and the download file name.
    $reportFilters = array_values(array_filter([
        ($filters['from'] || $filters['to']) ? 'Period: '.($filters['from'] ?: 'start').' to '.($filters['to'] ?: 'today') : null,
        $filters['categories'] ? 'Category: '.implode(', ', $filters['categories']) : null,
        $filters['methods'] ? 'Payment method: '.implode(', ', $filters['methods']) : null,
        $filters['q'] ? 'Search: '.$filters['q'] : null,
    ]));
    $fileName = 'accounting-'.($type ?: 'all').'-'.now()->format('Y-m-d');
    // Tabs keep the current filters and only swap the type.
    $tabQuery = fn (string $t) => array_filter(request()->except('type', 'page') + ['type' => $t]);
@endphp
<x-layouts.dashboard :title="$title">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-navy">📒 {{ $title }}</h2>
            <p class="text-sm text-gray-500">{{ number_format($rows->count()) }} {{ Str::plural('entry', $rows->count()) }}{{ $hasFilters ? ' match the filter' : '' }}</p>
        </div>
        <a href="{{ route('dashboard.accounting.create', array_filter(['type' => $type])) }}" class="btn-maroon justify-center">+ Add Entry</a>
    </div>

    {{-- Totals for the current filter --}}
    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-lg border-l-4 border-green-500 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Income</div>
            <div class="mt-1 text-2xl font-extrabold text-green-700">Rs. {{ number_format($income, 2) }}</div>
        </div>
        <div class="rounded-lg border-l-4 border-red-500 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Expense</div>
            <div class="mt-1 text-2xl font-extrabold text-red-700">Rs. {{ number_format($expense, 2) }}</div>
        </div>
        <div class="rounded-lg border-l-4 border-navy bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Balance</div>
            <div class="mt-1 text-2xl font-extrabold {{ $balance < 0 ? 'text-red-700' : 'text-navy' }}">{{ $balance < 0 ? '- ' : '' }}Rs. {{ number_format(abs($balance), 2) }}</div>
        </div>
    </div>

    {{-- All / Income / Expense --}}
    <div class="mt-4 flex gap-1 overflow-x-auto rounded-lg bg-white p-1 shadow-sm">
        @foreach ($tabs as $tabType => $tabLabel)
        <a href="{{ route('dashboard.accounting.index', $tabQuery($tabType)) }}" class="flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 py-2 text-sm font-semibold transition {{ (string) $type === $tabType ? 'bg-navy text-white shadow' : 'text-gray-600 hover:bg-gray-100' }}">
            {{ $tabLabel }}
            <span class="rounded-full px-2 text-xs {{ (string) $type === $tabType ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts[$tabType ?: 'all'] }}</span>
        </a>
        @endforeach
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif

    {{-- Filter + export --}}
    <form method="GET" action="{{ route('dashboard.accounting.index') }}" id="filter-form" class="mt-4 rounded-lg bg-white p-4 shadow-md">
        @if ($type) <input type="hidden" name="type" value="{{ $type }}"> @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="f-from" class="text-sm font-semibold text-gray-700">From date</label>
                <input type="date" id="f-from" name="from" value="{{ $filters['from'] }}" class="{{ $input }}">
            </div>
            <div>
                <label for="f-to" class="text-sm font-semibold text-gray-700">To date</label>
                <input type="date" id="f-to" name="to" value="{{ $filters['to'] }}" class="{{ $input }}">
            </div>
            <div>
                <label for="f-q" class="text-sm font-semibold text-gray-700">Search</label>
                <input type="search" id="f-q" name="q" value="{{ $filters['q'] }}" placeholder="Description, reference no., remarks" class="{{ $input }}">
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <fieldset>
                <legend class="text-sm font-semibold text-gray-700">Category</legend>
                <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-2 sm:grid-cols-3">
                    @foreach ($categoryOptions as $option)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="categories[]" value="{{ $option }}" @checked(in_array($option, $filters['categories'], true)) class="rounded border-gray-300 text-maroon focus:ring-maroon">
                        <span class="truncate">{{ $option }}</span>
                    </label>
                    @endforeach
                </div>
            </fieldset>
            <fieldset>
                <legend class="text-sm font-semibold text-gray-700">Payment method</legend>
                <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-2 sm:grid-cols-3">
                    @foreach ($methodOptions as $option)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="methods[]" value="{{ $option }}" @checked(in_array($option, $filters['methods'], true)) class="rounded border-gray-300 text-maroon focus:ring-maroon">
                        <span class="truncate">{{ $option }}</span>
                    </label>
                    @endforeach
                </div>
            </fieldset>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-2 border-t pt-4">
            <button type="submit" class="rounded-md bg-navy px-5 py-2 text-sm font-semibold text-white hover:bg-navy-800">Apply filter</button>
            @if ($hasFilters)
            <a href="{{ route('dashboard.accounting.index', array_filter(['type' => $type])) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
            <span class="hidden text-xs text-gray-400 sm:inline">Downloads contain the {{ number_format($rows->count()) }} filtered {{ Str::plural('entry', $rows->count()) }}.</span>
            <div class="ml-auto flex gap-2">
                <button type="button" id="dl-pdf" class="btn-maroon !px-4 !py-2 text-sm">⬇ PDF</button>
                <button type="button" id="dl-xlsx" class="inline-flex items-center gap-2 rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-green-800">⬇ Excel</button>
            </div>
        </div>
        <p id="dl-status" class="mt-2 hidden text-xs font-semibold text-gray-500" role="status"></p>
    </form>

    <div class="mt-4 rounded-lg bg-white p-3 shadow-md sm:p-4">
        <table id="resource-table" class="display w-full text-sm" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                <tr>
                    <td data-order="{{ $row->date->format('Y-m-d') }}" class="whitespace-nowrap">{{ $row->date->format('d M, Y') }}</td>
                    <td><span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $row->isIncome() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $row->isIncome() ? 'Income' : 'Expense' }}</span></td>
                    <td>{{ $row->category }}</td>
                    <td class="min-w-[12rem]">
                        <div class="font-semibold text-navy">{{ $row->description }}</div>
                        @if ($row->remarks)<div class="text-xs text-gray-500">{{ Str::limit($row->remarks, 80) }}</div>@endif
                    </td>
                    <td class="whitespace-nowrap">{{ $row->payment_method }}</td>
                    <td class="whitespace-nowrap font-mono text-xs">{{ $row->reference_no ?: '—' }}</td>
                    <td data-order="{{ $row->amount }}" class="whitespace-nowrap text-right font-bold {{ $row->isIncome() ? 'text-green-700' : 'text-red-700' }}">{{ $row->isIncome() ? '+' : '−' }} {{ number_format((float) $row->amount, 2) }}</td>
                    <td class="whitespace-nowrap text-right">
                        <a href="{{ route('dashboard.accounting.edit', $row) }}" class="mr-3 text-xs font-semibold text-navy hover:underline">Edit</a>
                        <form method="POST" action="{{ route('dashboard.accounting.destroy', $row) }}" class="inline" onsubmit="return confirm('Delete this entry? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        new DataTable('#resource-table', {
            responsive: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            searching: false, // the filter above searches on the server so the downloads match what is listed
            columnDefs: [
                { responsivePriority: 1, targets: 3 },
                { responsivePriority: 2, targets: 6 },
                { responsivePriority: 3, targets: -1 },
                { orderable: false, targets: -1 },
            ],
            language: { emptyTable: 'No entries match.' },
        });

        (function () {
            const rows = @json($export);
            const report = {
                org: @json($siteSettings->site_name_en),
                orgNp: @json($siteSettings->site_name_np),
                title: @json($title.' Report'),
                income: @json($income),
                expense: @json($expense),
                filters: @json($reportFilters),
                file: @json($fileName),
            };
            // "Total" lines follow the tab: the Income tab only reports income, and so on.
            const showIncome = @json($type !== 'expense');
            const showExpense = @json($type !== 'income');
            const money = (n) => Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
            const buttons = document.querySelectorAll('#dl-pdf, #dl-xlsx');
            const status = document.getElementById('dl-status');

            async function run(label, job) {
                buttons.forEach((b) => (b.disabled = true));
                status.textContent = label;
                status.classList.remove('hidden');
                try {
                    await job();
                    status.classList.add('hidden');
                } catch (e) {
                    console.error(e);
                    status.textContent = 'Could not create the file. Check your internet connection and try again.';
                } finally {
                    buttons.forEach((b) => (b.disabled = false));
                }
            }

            // ---------------------------------------------------------------- Excel
            document.getElementById('dl-xlsx').addEventListener('click', () => run('Preparing Excel…', async () => {
                const head = ['Date', 'Type', 'Category', 'Description', 'Amount', 'Payment Method', 'Reference No.', 'Remarks'];
                const body = rows.map((r) => [r.date, r.type, r.category, r.description, r.amount, r.method, r.reference, r.remarks]);
                const totals = [[]];
                if (showIncome) totals.push(['', '', '', 'Total Income', report.income]);
                if (showExpense) totals.push(['', '', '', 'Total Expense', report.expense]);
                if (showIncome && showExpense) totals.push(['', '', '', 'Balance', report.income - report.expense]);

                const sheet = XLSX.utils.aoa_to_sheet([head, ...body, ...totals]);
                sheet['!cols'] = [{ wch: 12 }, { wch: 9 }, { wch: 18 }, { wch: 40 }, { wch: 14 }, { wch: 15 }, { wch: 16 }, { wch: 30 }];
                for (let r = 2; r <= body.length + totals.length + 1; r++) {
                    const cell = sheet['E' + r];
                    if (cell && typeof cell.v === 'number') cell.z = '#,##0.00';
                }
                const book = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(book, sheet, 'Accounting');
                XLSX.writeFile(book, report.file + '.xlsx');
            }));

            // ---------------------------------------------------------------- PDF
            // The browser lays the pages out (so Nepali text renders correctly), each page is captured as an image.
            const PAGE_W = 1123, PAGE_H = 794, PAD = 36; // A4 landscape at 96 dpi

            const css = `
                .rp { box-sizing: border-box; width: ${PAGE_W}px; height: ${PAGE_H}px; padding: ${PAD}px; background: #fff; color: #1f2937; font-size: 12px; line-height: 1.35; position: relative; overflow: hidden; }
                .rp * { box-sizing: border-box; }
                .rp table { width: 100%; border-collapse: collapse; table-layout: fixed; }
                .rp th { padding: 7px 8px; background: #001F5B; color: #fff; font-size: 10.5px; text-align: left; letter-spacing: .04em; text-transform: uppercase; }
                .rp td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; word-break: break-word; }
                .rp .r { text-align: right; }
                .rp .inc { color: #15803d; } .rp .exp { color: #b91c1c; }
                .rp .tot td { border-bottom: 0; padding: 4px 8px; font-weight: 700; }
                .rp .foot { position: absolute; left: ${PAD}px; right: ${PAD}px; bottom: 16px; display: flex; justify-content: space-between; color: #9ca3af; font-size: 10px; }
            `;
            const colgroup = '<colgroup><col style="width:92px"><col style="width:70px"><col style="width:130px"><col><col style="width:100px"><col style="width:120px"><col style="width:120px"></colgroup>';
            const thead = '<thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Description</th><th>Method</th><th>Reference</th><th class="r">Amount</th></tr></thead>';

            const rowHtml = (r) => `<tr>
                <td style="white-space:nowrap">${esc(r.date)}</td>
                <td class="${r.type === 'Income' ? 'inc' : 'exp'}" style="font-weight:700">${esc(r.type)}</td>
                <td>${esc(r.category)}</td>
                <td><b>${esc(r.description)}</b>${r.remarks ? `<div style="color:#6b7280;font-size:10.5px">${esc(r.remarks)}</div>` : ''}</td>
                <td>${esc(r.method)}</td>
                <td>${esc(r.reference) || '—'}</td>
                <td class="r ${r.type === 'Income' ? 'inc' : 'exp'}" style="font-weight:700;white-space:nowrap">${r.type === 'Income' ? '+' : '−'} ${money(r.amount)}</td>
            </tr>`;

            const balance = report.income - report.expense;
            const totalRows = [];
            if (showIncome) totalRows.push(['Total Income', 'inc', `Rs. ${money(report.income)}`]);
            if (showExpense) totalRows.push(['Total Expense', 'exp', `Rs. ${money(report.expense)}`]);
            if (showIncome && showExpense) totalRows.push(['Balance', balance < 0 ? 'exp' : '', `${balance < 0 ? '− ' : ''}Rs. ${money(Math.abs(balance))}`]);
            const totalsHtml = totalRows.map(([label, cls, value], i) => `<tr class="tot">
                <td colspan="6" class="r" style="${i === 0 ? 'padding-top:10px' : ''}">${label}</td>
                <td class="r ${cls}" style="${i === 0 ? 'padding-top:10px' : ''}${label === 'Balance' ? ';border-top:2px solid #001F5B' : ''}">${value}</td></tr>`).join('');

            const firstHead = () => `<div style="display:flex;justify-content:space-between;align-items:flex-end;border-bottom:3px solid #D4AF37;padding-bottom:8px">
                    <div><div style="font-size:20px;font-weight:800;color:#8B0000">${esc(report.orgNp)}</div><div style="font-size:11px;color:#6b7280">${esc(report.org)}</div></div>
                    <div style="text-align:right"><div style="font-size:16px;font-weight:800;color:#001F5B">${esc(report.title)}</div><div style="font-size:10.5px;color:#6b7280">Generated ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</div></div>
                </div>
                <div style="margin:8px 0 10px;color:#4b5563;font-size:11px">${report.filters.length ? report.filters.map(esc).join(' &nbsp;·&nbsp; ') : 'All entries'} &nbsp;·&nbsp; <b>${rows.length}</b> ${rows.length === 1 ? 'entry' : 'entries'}</div>`;
            const nextHead = () => `<div style="border-bottom:2px solid #D4AF37;padding-bottom:6px;margin-bottom:10px;font-weight:700;color:#001F5B">${esc(report.title)} <span style="font-weight:400;color:#6b7280">(continued)</span></div>`;

            async function buildPdf() {
                await (document.fonts ? document.fonts.ready : Promise.resolve());

                const host = document.createElement('div');
                host.style.cssText = 'position:fixed;left:-20000px;top:0;';
                host.innerHTML = `<style>${css}</style>`;
                document.body.appendChild(host);

                try {
                    // Measure every row once so pages can be filled without splitting a row.
                    const probe = document.createElement('div');
                    probe.className = 'rp';
                    probe.style.height = 'auto';
                    probe.innerHTML = `<div id="p-head">${firstHead()}</div><div id="p-next">${nextHead()}</div>
                        <table>${colgroup}${thead}<tbody>${rows.map(rowHtml).join('')}</tbody><tfoot>${totalsHtml}</tfoot></table>`;
                    host.appendChild(probe);
                    const headH = probe.querySelector('#p-head').offsetHeight + 10;
                    const nextH = probe.querySelector('#p-next').offsetHeight + 10;
                    const theadH = probe.querySelector('thead').offsetHeight;
                    const rowH = [...probe.querySelectorAll('tbody tr')].map((tr) => tr.offsetHeight);
                    const totalsH = [...probe.querySelectorAll('tfoot tr')].reduce((sum, tr) => sum + tr.offsetHeight, 0);
                    host.removeChild(probe);

                    const room = PAGE_H - PAD * 2 - 24 - theadH; // 24px reserved for the page footer
                    const pages = [];
                    let current = [], used = headH;
                    rowH.forEach((h, i) => {
                        if (current.length && used + h > room) { pages.push(current); current = []; used = nextH; }
                        current.push(i);
                        used += h;
                    });
                    pages.push(current);
                    if (used + totalsH > room && current.length) pages.push([]); // totals get their own page when the last one is full

                    const pdf = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                    for (let p = 0; p < pages.length; p++) {
                        const page = document.createElement('div');
                        page.className = 'rp';
                        const last = p === pages.length - 1;
                        const empty = rows.length === 0 ? '<tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:24px">No entries match.</td></tr>' : '';
                        page.innerHTML = (p === 0 ? firstHead() : nextHead())
                            + `<table>${colgroup}${thead}<tbody>${pages[p].map((i) => rowHtml(rows[i])).join('')}${empty}</tbody>${last ? `<tfoot>${totalsHtml}</tfoot>` : ''}</table>`
                            + `<div class="foot"><span>${esc(report.org)}</span><span>Page ${p + 1} of ${pages.length}</span></div>`;
                        host.appendChild(page);
                        const image = await htmlToImage.toJpeg(page, { pixelRatio: 2, quality: 0.92, backgroundColor: '#ffffff', width: PAGE_W, height: PAGE_H });
                        if (p > 0) pdf.addPage();
                        pdf.addImage(image, 'JPEG', 0, 0, 297, 210);
                        host.removeChild(page);
                    }
                    pdf.save(report.file + '.pdf');
                } finally {
                    host.remove();
                }
            }

            document.getElementById('dl-pdf').addEventListener('click', () => run('Preparing PDF…', buildPdf));
        })();
    </script>
    @endpush
</x-layouts.dashboard>
