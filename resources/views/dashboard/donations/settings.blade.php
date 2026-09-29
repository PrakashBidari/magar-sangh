<x-layouts.dashboard title="Lakhan Thapa Pratisthan Settings">
    <div class="mx-auto max-w-5xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-navy">⚙️ Lakhan Thapa Pratisthan Settings</h2>
                <p class="text-sm text-gray-500">Content shown above the donation list on the public Lakhan Thapa Pratisthan page.</p>
            </div>
            <a href="{{ route('donation-list') }}" target="_blank" class="text-sm font-semibold text-maroon hover:underline">View page ↗</a>
        </div>

        @if (session('dashboard-status'))
        <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
        @endif

        @error('donation_page_content')
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('dashboard.donation-settings.update') }}" id="donation-settings-form" class="card space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="donation_page_content" class="text-sm font-semibold text-gray-700">Page Content</label>
                <p class="mb-2 text-xs text-gray-500">Add text, images (🖼 button) and PDF files (📎 file button, max 10 MB). PDFs are shown with a preview on the page.</p>
                <textarea id="donation_page_content" name="donation_page_content">{{ old('donation_page_content', $settings->donation_page_content) }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" id="donation-settings-submit" class="btn-maroon justify-center !px-6 !py-2.5 text-sm">Save</button>
            </div>
        </form>

        {{-- Titled PDF files: listed on the page with a Download button --}}
        <div class="card mt-6">
            <h3 class="font-bold text-navy">📄 PDF Files</h3>
            <p class="text-xs text-gray-500">Each PDF is listed on the page with its title and a Download button (max 10 MB).</p>

            <form method="POST" action="{{ route('dashboard.donation-settings.documents.store') }}" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                @csrf
                <div>
                    <label for="pdf-title" class="text-sm font-semibold text-gray-700">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="pdf-title" name="title" value="{{ old('title') }}" maxlength="255" required class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="pdf-file" class="text-sm font-semibold text-gray-700">PDF File <span class="text-red-500">*</span></label>
                    <input type="file" id="pdf-file" name="pdf" accept="application/pdf,.pdf" required class="mt-1 w-full text-sm">
                    @error('pdf') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-maroon justify-center !px-5 !py-2.5 text-sm">+ Upload PDF</button>
            </form>

            <div class="mt-5 divide-y divide-gray-100 rounded-md border border-gray-100">
                @forelse ($documents as $document)
                <div class="flex items-center gap-3 px-4 py-3">
                    <span class="text-xl">📄</span>
                    <a href="{{ $document->file_url }}" target="_blank" rel="noopener" class="min-w-0 flex-1 truncate text-sm font-semibold text-navy hover:underline">{{ $document->title }}</a>
                    <form method="POST" action="{{ route('dashboard.donation-settings.documents.destroy', $document) }}" onsubmit="return confirm('Delete this PDF? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-md px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
                    </form>
                </div>
                @empty
                <p class="px-4 py-6 text-center text-sm text-gray-400">No PDF files uploaded yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const form = document.getElementById('donation-settings-form');
            let editor = null;

            NepalRichEditor.init(document.getElementById('donation_page_content'))
                .then((instance) => (editor = instance))
                .catch(() => {});

            form.addEventListener('submit', () => {
                editor?.synchronizeValues();
                const button = document.getElementById('donation-settings-submit');
                button.disabled = true;
                button.textContent = 'Saving…';
            });
        })();
    </script>
    @endpush
</x-layouts.dashboard>
