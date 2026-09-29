<x-layouts.dashboard title="Membership Settings">
    <div class="mx-auto max-w-3xl">
        <div class="mb-4">
            <h2 class="text-xl font-bold text-navy">⚙️ Membership Settings</h2>
            <p class="text-sm text-gray-500">These settings apply to every membership ID card.</p>
        </div>

        @if (session('dashboard-status'))
        <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
        @endif

        @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold">Please fix the following:</div>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('dashboard.membership.settings.update') }}" enctype="multipart/form-data" class="card space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="authorized_signature" class="text-sm font-semibold text-gray-700">Authorized Signature</label>
                <p class="text-xs text-gray-500">Printed above "Authorized signature" on all ID cards. Use a PNG with a transparent or white background (max 2 MB).</p>
                <input type="file" id="authorized_signature" name="authorized_signature" accept="image/*" class="mt-2 w-full text-sm">
                @error('authorized_signature') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-3 flex items-center gap-4">
                    <div id="signature-preview" class="flex h-20 w-56 items-center justify-center rounded-md border border-dashed border-gray-300 bg-white p-2">
                        @if ($settings->authorized_signature_url)
                        <img src="{{ $settings->authorized_signature_url }}" alt="Authorized signature" class="max-h-full max-w-full object-contain">
                        @else
                        <span class="text-xs text-gray-400">No signature uploaded</span>
                        @endif
                    </div>
                    @if ($settings->authorized_signature_url)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="remove_signature" value="1" class="rounded border-gray-300 text-maroon focus:ring-maroon">
                        Remove signature
                    </label>
                    @endif
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-maroon justify-center !px-6 !py-2.5 text-sm">Save Settings</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.getElementById('authorized_signature').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'max-h-full max-w-full object-contain';
            document.getElementById('signature-preview').replaceChildren(img);
        });
    </script>
    @endpush
</x-layouts.dashboard>
