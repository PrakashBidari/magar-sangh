@php
    $editing = (bool) $sifaris;
    $title = $editing ? 'Edit Sifaris Request' : 'Sifaris Request';
    $action = $editing ? route('dashboard.sifaris.update', $sifaris) : route('dashboard.my-sifaris.store');
    $backUrl = $editing ? route('dashboard.sifaris.show', $sifaris) : route('dashboard.my-sifaris.index');
    $v = fn (string $name) => old($name, $values[$name] ?? null);
    $input = 'mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon';
    $sectionTitle = 'mb-4 flex items-center gap-2 border-b pb-2 text-sm font-bold uppercase tracking-wider text-navy';
    $req = '<span class="text-red-500">*</span>';
    $hint = 'mt-1 text-xs text-gray-400';
@endphp
<x-layouts.dashboard :title="$title">
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-navy">📜 {{ $title }}</h2>
                @unless ($editing)
                <p class="text-sm text-gray-500">सिफारिस — a letter from the Nepal Magar Association confirming that you are Magar by descent. Please write names in Nepali, as they should appear on the letter.</p>
                @endunless
            </div>
            <a href="{{ $backUrl }}" class="text-sm font-semibold text-maroon hover:underline">← Back</a>
        </div>

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

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="sifaris-form" class="space-y-5">
            @csrf
            @if ($editing) @method('PUT') @endif

            {{-- 1. Applicant and family --}}
            <section class="card grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                <h3 class="{{ $sectionTitle }} sm:col-span-2">1 · Applicant &amp; family (निवेदक र परिवार)</h3>
                <div class="sm:col-span-2">
                    <label for="applicant_name" class="text-sm font-semibold text-gray-700">Applicant's full name (निवेदकको नाम थर) {!! $req !!}</label>
                    <input type="text" id="applicant_name" name="applicant_name" value="{{ $v('applicant_name') }}" required maxlength="100" placeholder="जस्तै: सीता थापा मगर" class="{{ $input }} np">
                    @error('applicant_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="parent_name" class="text-sm font-semibold text-gray-700">Father's / husband's name (बुबा / पतिको नाम) {!! $req !!}</label>
                    <input type="text" id="parent_name" name="parent_name" value="{{ $v('parent_name') }}" required maxlength="100" class="{{ $input }} np">
                    @error('parent_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="parent_relation" class="text-sm font-semibold text-gray-700">Your relation to him (नाता) {!! $req !!}</label>
                    <select id="parent_relation" name="parent_relation" required class="{{ $input }}">
                        <option value="">Select…</option>
                        @foreach (\App\Models\SifarisRequest::PARENT_RELATIONS as $value => $label)
                        <option value="{{ $value }}" @selected($v('parent_relation') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('parent_relation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="grandparent_name" class="text-sm font-semibold text-gray-700">Grandfather's / father-in-law's name (बाजे / ससुराको नाम) {!! $req !!}</label>
                    <input type="text" id="grandparent_name" name="grandparent_name" value="{{ $v('grandparent_name') }}" required maxlength="100" class="{{ $input }} np">
                    @error('grandparent_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="grandparent_relation" class="text-sm font-semibold text-gray-700">Your relation to him (नाता) {!! $req !!}</label>
                    <select id="grandparent_relation" name="grandparent_relation" required class="{{ $input }}">
                        <option value="">Select…</option>
                        @foreach (\App\Models\SifarisRequest::GRANDPARENT_RELATIONS as $value => $label)
                        <option value="{{ $value }}" @selected($v('grandparent_relation') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('grandparent_relation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <p class="{{ $hint }} sm:col-span-2">A married woman usually writes her husband's name and her father-in-law's name, with the relations "Wife" and "Daughter-in-law".</p>
            </section>

            {{-- 2. Permanent address --}}
            <section class="card grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                <h3 class="{{ $sectionTitle }} sm:col-span-2">2 · Permanent address (स्थायी ठेगाना)</h3>
                <div>
                    <label for="province" class="text-sm font-semibold text-gray-700">Province (प्रदेश) {!! $req !!}</label>
                    <select id="province" name="province" required class="{{ $input }} np">
                        <option value="">Select…</option>
                        @foreach (\App\Models\SifarisRequest::PROVINCES as $province)
                        <option value="{{ $province }}" @selected($v('province') === $province)>{{ $province }}</option>
                        @endforeach
                    </select>
                    @error('province')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="district" class="text-sm font-semibold text-gray-700">District (जिल्ला) {!! $req !!}</label>
                    <input type="text" id="district" name="district" value="{{ $v('district') }}" required maxlength="60" placeholder="जस्तै: रोल्पा" class="{{ $input }} np">
                    @error('district')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="municipality" class="text-sm font-semibold text-gray-700">Municipality / Rural municipality (न.पा. / गा.पा.) {!! $req !!}</label>
                    <input type="text" id="municipality" name="municipality" value="{{ $v('municipality') }}" required maxlength="100" placeholder="जस्तै: रोल्पा न.पा." class="{{ $input }} np">
                    @error('municipality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ward_no" class="text-sm font-semibold text-gray-700">Ward no. (वडा नं.) {!! $req !!}</label>
                    <input type="text" id="ward_no" name="ward_no" value="{{ $v('ward_no') }}" required maxlength="10" placeholder="जस्तै: ४" class="{{ $input }} np">
                    @error('ward_no')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </section>

            {{-- 3. Contact and photo --}}
            <section class="card">
                <h3 class="{{ $sectionTitle }}">3 · Contact &amp; photo</h3>
                <div class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                    <div>
                        <label for="mobile" class="text-sm font-semibold text-gray-700">Mobile number {!! $req !!}</label>
                        <input type="tel" id="mobile" name="mobile" value="{{ $v('mobile') }}" required inputmode="tel" autocomplete="tel" placeholder="98XXXXXXXX" class="{{ $input }}">
                        @error('mobile')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ $v('email') }}" autocomplete="email" class="{{ $input }}">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-5">
                    <label for="photo" class="text-sm font-semibold text-gray-700">Passport-size photo {!! $editing ? '' : $req !!}</label>
                    <div class="mt-2 flex items-start gap-3">
                        <img id="photo-preview" src="{{ $sifaris?->photo_url ?: '' }}" alt="" class="h-36 w-28 shrink-0 rounded-md border border-gray-200 bg-gray-50 object-cover {{ $sifaris?->photo_url ? '' : 'hidden' }}">
                        <div class="min-w-0 flex-1">
                            <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp" @required(! $editing) class="w-full text-sm">
                            <p class="{{ $hint }}">Recent passport-size photo (35 × 45 mm), clear face, plain background. JPG/PNG/WEBP, up to 3 MB.{{ $editing ? ' Leave empty to keep the current photo.' : '' }}</p>
                        </div>
                    </div>
                    @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </section>

            @unless ($editing)
            <section class="card">
                <label class="flex items-start gap-3 text-sm font-semibold text-gray-700">
                    <input type="checkbox" name="declaration" value="1" @checked(old('declaration')) class="mt-0.5 rounded border-gray-300 text-maroon focus:ring-maroon">
                    <span>I confirm that the details above are true, and that I am Magar by descent. {!! $req !!}</span>
                </label>
                @error('declaration')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </section>
            @endunless

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ $backUrl }}" class="rounded-md border border-gray-300 bg-white px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" id="sifaris-submit" class="btn-maroon justify-center">{{ $editing ? 'Save Changes' : 'Submit Request' }}</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            const form = document.getElementById('sifaris-form');
            const photo = document.getElementById('photo');
            const preview = document.getElementById('photo-preview');

            photo.addEventListener('change', () => {
                const file = photo.files && photo.files[0];
                if (!file) return;
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            });

            form.addEventListener('submit', () => {
                const button = document.getElementById('sifaris-submit');
                button.disabled = true;
                button.textContent = 'Submitting…';
            });
        })();
    </script>
    @endpush
</x-layouts.dashboard>
