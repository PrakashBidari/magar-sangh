@php
    $input = 'mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon';
@endphp
<x-layouts.dashboard title="Sifaris Settings">
    <div>
        <div class="mb-4">
            <h2 class="text-xl font-bold text-navy">⚙️ Sifaris Settings</h2>
            <p class="text-sm text-gray-500">The signature, signatory and contact details printed at the bottom of every sifaris letter.</p>
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

        <form method="POST" action="{{ route('dashboard.sifaris.settings.update') }}" enctype="multipart/form-data" class="card space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="signature" class="text-sm font-semibold text-gray-700">Signature image</label>
                <p class="text-xs text-gray-500">Printed above the name. Use a PNG with a transparent or white background (max 2 MB).</p>
                <input type="file" id="signature" name="signature" accept="image/png,image/jpeg,image/webp" class="mt-2 w-full text-sm">
                @error('signature') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-3 flex flex-wrap items-center gap-4">
                    <div id="signature-preview" class="flex h-28 w-72 shrink-0 items-center justify-center rounded-md border border-dashed border-gray-300 bg-white p-2">
                        @if ($settings->signature_url)
                        <img src="{{ $settings->signature_url }}" alt="Signature" class="max-h-full max-w-full object-contain">
                        @else
                        <span class="text-xs text-gray-400">No signature uploaded</span>
                        @endif
                    </div>
                    @if ($settings->signature_url)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="remove_signature" value="1" class="rounded border-gray-300 text-maroon focus:ring-maroon">
                        Remove signature
                    </label>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="signatory_name" class="text-sm font-semibold text-gray-700">Name below the signature <span class="text-red-600">*</span></label>
                    <input type="text" id="signatory_name" name="signatory_name" value="{{ old('signatory_name', $settings->signatory_name) }}" maxlength="100" required class="{{ $input }} np">
                </div>
                <div>
                    <label for="signatory_title" class="text-sm font-semibold text-gray-700">Position</label>
                    <input type="text" id="signatory_title" name="signatory_title" value="{{ old('signatory_title', $settings->signatory_title) }}" maxlength="100" placeholder="जस्तै: महासचिव" class="{{ $input }} np">
                    <p class="mt-1 text-xs text-gray-500">Printed in brackets under the name.</p>
                </div>
                <div class="md:col-span-2">
                    <label for="phone" class="text-sm font-semibold text-gray-700">Telephone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $settings->phone) }}" maxlength="150" class="{{ $input }}">
                    <p class="mt-1 text-xs text-gray-500">Separate numbers with commas. There is room for about four numbers on the letter.</p>
                </div>
                <div>
                    <label for="email" class="text-sm font-semibold text-gray-700">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $settings->email) }}" maxlength="150" class="{{ $input }}">
                </div>
                <div>
                    <label for="website" class="text-sm font-semibold text-gray-700">Website</label>
                    <input type="text" id="website" name="website" value="{{ old('website', $settings->website) }}" maxlength="150" placeholder="magarsangh.org.np" class="{{ $input }}">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-maroon justify-center !px-6 !py-2.5 text-sm">Save Settings</button>
            </div>
        </form>

        {{-- The letter with the saved settings (applicant fields left empty) --}}
        <div class="card mt-5">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Letter with the saved settings</h3>
            @include('dashboard.sifaris._letter', ['sifaris' => null])
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('signature').addEventListener('change', (e) => {
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
