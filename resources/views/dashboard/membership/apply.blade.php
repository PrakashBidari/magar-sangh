@php
    $editing = (bool) $membership;
    $title = $editing ? 'Edit Application' : 'Membership Application';
    $action = $editing ? route('dashboard.membership.update', $membership) : route('dashboard.my-membership.store');
    $backUrl = $editing ? route('dashboard.membership.show', $membership) : route('dashboard.my-membership.show');
    $v = fn (string $name) => old($name, $values[$name] ?? null);
    $input = 'mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon';
    $sectionTitle = 'mb-4 flex items-center gap-2 border-b pb-2 text-sm font-bold uppercase tracking-wider text-navy';
    $req = '<span class="text-red-500">*</span>';
    $selectedType = (int) $v('membership_type_id');
@endphp
<x-layouts.dashboard :title="$title">
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-navy">🎫 {{ $title }}</h2>
                @unless ($editing)
                <p class="text-sm text-gray-500">Nepal Magar Association — choose your membership category and fill in your details.</p>
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

        @if ($types->isEmpty())
        <div class="card text-sm text-gray-600">No membership types are open for applications right now. Please check back later.</div>
        @else
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="membership-form" class="space-y-5">
            @csrf
            @if ($editing) @method('PUT') @endif

            {{-- 1. Membership type --}}
            <section class="card">
                <h3 class="{{ $sectionTitle }}">1 · Membership type {!! $req !!}</h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($types as $type)
                    <label class="group relative cursor-pointer">
                        <input type="radio" name="membership_type_id" value="{{ $type->id }}" data-fee="{{ $type->requiresPayment() ? $type->fee_label : '' }}" @checked($selectedType === $type->id) class="peer sr-only">
                        <div class="h-full rounded-lg border-2 border-gray-200 p-4 transition peer-checked:border-maroon peer-checked:bg-maroon-50 peer-focus-visible:ring-2 peer-focus-visible:ring-maroon group-hover:border-maroon-300 peer-checked:[&_.tick]:flex">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-bold text-navy">{{ $type->name_en }}</div>
                                    <div class="np text-sm text-gray-500">{{ $type->name_np }}</div>
                                </div>
                                <span class="tick mt-0.5 hidden h-5 w-5 shrink-0 items-center justify-center rounded-full bg-maroon text-xs text-white">✓</span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-1.5 text-xs font-semibold">
                                <span class="rounded-full bg-navy-50 px-2 py-0.5 text-navy">⏱ {{ $type->durationLabel() }}</span>
                                <span class="rounded-full px-2 py-0.5 {{ $type->requiresPayment() ? 'bg-gold-100 text-gold-700' : 'bg-green-100 text-green-700' }}">{{ $type->fee_label }}</span>
                                @unless ($type->is_active)
                                <span class="rounded-full bg-gray-200 px-2 py-0.5 text-gray-600">Closed</span>
                                @endunless
                            </div>
                            @if ($type->description)
                            <p class="mt-2 text-xs text-gray-500">{{ $type->description }}</p>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('membership_type_id')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            </section>

            {{-- 2. Personal details --}}
            <section class="card grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                <h3 class="{{ $sectionTitle }} sm:col-span-2">2 · Personal details</h3>
                <div>
                    <label for="full_name" class="text-sm font-semibold text-gray-700">Full name {!! $req !!}</label>
                    <input type="text" id="full_name" name="full_name" value="{{ $v('full_name') }}" required autocomplete="given-name" class="{{ $input }}">
                    @error('full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="surname" class="text-sm font-semibold text-gray-700">Surname {!! $req !!}</label>
                    <input type="text" id="surname" name="surname" value="{{ $v('surname') }}" required autocomplete="family-name" class="{{ $input }}">
                    @error('surname')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="date_of_birth" class="text-sm font-semibold text-gray-700">Date of birth {!! $req !!}</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ $v('date_of_birth') }}" max="{{ today()->subDay()->format('Y-m-d') }}" required class="{{ $input }}">
                    @error('date_of_birth')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="occupation" class="text-sm font-semibold text-gray-700">Occupation / Business {!! $req !!}</label>
                    <input type="text" id="occupation" name="occupation" value="{{ $v('occupation') }}" required class="{{ $input }}">
                    @error('occupation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="mobile" class="text-sm font-semibold text-gray-700">Mobile number {!! $req !!}</label>
                    <input type="tel" id="mobile" name="mobile" value="{{ $v('mobile') }}" required inputmode="tel" autocomplete="tel" placeholder="98XXXXXXXX" class="{{ $input }}">
                    @error('mobile')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="text-sm font-semibold text-gray-700">Email {!! $req !!}</label>
                    <input type="email" id="email" name="email" value="{{ $v('email') }}" required autocomplete="email" class="{{ $input }}">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </section>

            {{-- 3. Address --}}
            <section class="card grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                <h3 class="{{ $sectionTitle }} sm:col-span-2">3 · Address</h3>
                <div>
                    <label for="province" class="text-sm font-semibold text-gray-700">Province {!! $req !!}</label>
                    <select id="province" name="province" required class="{{ $input }}">
                        <option value="">Select province</option>
                        @foreach ($provinces as $province => $districts)
                        <option value="{{ $province }}" @selected($v('province') === $province)>{{ $province }}</option>
                        @endforeach
                    </select>
                    @error('province')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="district" class="text-sm font-semibold text-gray-700">District {!! $req !!}</label>
                    <select id="district" name="district" required data-selected="{{ $v('district') }}" class="{{ $input }}">
                        <option value="">Select province first</option>
                    </select>
                    @error('district')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="municipality" class="text-sm font-semibold text-gray-700">Municipality / Rural municipality {!! $req !!}</label>
                    <select id="municipality" name="municipality" required data-selected="{{ $v('municipality') }}" class="{{ $input }}">
                        <option value="">Select district first</option>
                    </select>
                    @error('municipality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ward_no" class="text-sm font-semibold text-gray-700">Ward no. {!! $req !!}</label>
                    <input type="number" id="ward_no" name="ward_no" value="{{ $v('ward_no') }}" min="1" max="35" required inputmode="numeric" class="{{ $input }}">
                    @error('ward_no')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="permanent_address" class="text-sm font-semibold text-gray-700">Permanent address {!! $req !!}</label>
                    <input type="text" id="permanent_address" name="permanent_address" value="{{ $v('permanent_address') }}" required class="{{ $input }}">
                    @error('permanent_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <label for="current_address" class="text-sm font-semibold text-gray-700">Current address {!! $req !!}</label>
                        <label class="flex items-center gap-1.5 text-xs text-gray-500">
                            <input type="checkbox" id="same-address" class="rounded border-gray-300 text-maroon focus:ring-maroon"> Same as permanent address
                        </label>
                    </div>
                    <input type="text" id="current_address" name="current_address" value="{{ $v('current_address') }}" required class="{{ $input }}">
                    @error('current_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </section>

            {{-- 4. Photo, signature, voucher --}}
            <section class="card">
                <h3 class="{{ $sectionTitle }}">4 · Photograph, signature &amp; payment voucher</h3>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    @php
                        $uploads = [
                            ['name' => 'photo', 'label' => 'Photograph', 'required' => ! $editing, 'current' => $membership?->photo_url, 'hint' => 'Passport-size, clear face. JPG/PNG/WEBP, up to 3 MB.', 'box' => 'h-36 w-28'],
                            ['name' => 'signature', 'label' => 'Applicant\'s signature', 'required' => false, 'current' => $membership?->signature_url, 'hint' => 'Optional. Photo of your signature on white paper, up to 2 MB.', 'box' => 'h-24 w-40'],
                            ['name' => 'voucher', 'label' => 'Payment voucher', 'required' => false, 'current' => $membership?->voucher_path ? route('dashboard.membership.voucher', $membership) : null, 'hint' => 'Photo of your bank / e-wallet payment slip, up to 5 MB.', 'box' => 'h-36 w-28'],
                        ];
                    @endphp
                    @foreach ($uploads as $up)
                    <div class="min-w-0">
                        <label for="{{ $up['name'] }}" class="text-sm font-semibold text-gray-700">
                            {{ $up['label'] }}
                            @if ($up['required']) {!! $req !!} @endif
                            @if ($up['name'] === 'voucher') <span id="voucher-required" class="hidden">{!! $req !!}</span> @endif
                        </label>
                        <div class="mt-2 flex items-start gap-3">
                            <img data-preview-for="{{ $up['name'] }}" src="{{ $up['current'] ?: '' }}" alt="" class="{{ $up['box'] }} shrink-0 rounded-md border border-gray-200 bg-gray-50 object-contain {{ $up['current'] ? '' : 'hidden' }}">
                            <div class="min-w-0 flex-1">
                                <input type="file" id="{{ $up['name'] }}" name="{{ $up['name'] }}" accept="image/png,image/jpeg,image/webp" data-image-input class="w-full text-sm">
                                <p class="mt-1 text-xs text-gray-400">{{ $up['hint'] }}</p>
                                @if ($up['name'] === 'voucher')
                                <p id="voucher-fee" class="mt-1 hidden text-xs font-semibold text-maroon"></p>
                                @endif
                                @if ($editing && $up['name'] === 'signature' && $up['current'])
                                <label class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                    <input type="checkbox" name="remove_signature" value="1" class="rounded border-gray-300 text-maroon focus:ring-maroon"> Remove current signature
                                </label>
                                @endif
                            </div>
                        </div>
                        @error($up['name'])<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @endforeach
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Application date</label>
                        <input type="text" value="{{ ($membership?->applied_at ?? today())->format('d M, Y') }}" readonly class="{{ $input }} bg-gray-50 text-gray-500">
                    </div>
                    @if ($editing && $membership->isApproved())
                    <div>
                        <label for="expires_at" class="text-sm font-semibold text-gray-700">Valid until</label>
                        <input type="date" id="expires_at" name="expires_at" value="{{ $v('expires_at') }}" class="{{ $input }}">
                        <p class="mt-1 text-xs text-gray-400">Leave empty for a lifetime membership. Use this to extend or shorten the plan.</p>
                        @error('expires_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @endif
                </div>
            </section>

            {{-- 5. Declaration --}}
            @unless ($editing)
            <section class="card">
                <h3 class="{{ $sectionTitle }}">5 · Declaration</h3>
                <blockquote class="rounded-md border-l-4 border-gold bg-gold-50 px-4 py-3 text-sm italic text-gray-700">
                    “I hereby declare my commitment to respecting the constitution, objectives, policies, and rules of the Nepal Magar Association and to contributing to the unity and organizational strengthening of the Association, as well as the preservation and promotion of the Magar language, culture, traditions, identity, and rights.”
                </blockquote>
                <label class="mt-4 flex items-start gap-3 text-sm font-semibold text-gray-700">
                    <input type="checkbox" name="declaration" value="1" @checked(old('declaration')) class="mt-0.5 rounded border-gray-300 text-maroon focus:ring-maroon">
                    <span>I agree with the declaration above. {!! $req !!}</span>
                </label>
                @error('declaration')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </section>
            @endunless

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ $backUrl }}" class="rounded-md border border-gray-300 bg-white px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" id="membership-submit" class="btn-maroon justify-center">{{ $editing ? 'Save Changes' : 'Submit Application' }}</button>
            </div>
        </form>
        @endif
    </div>

    @push('scripts')
    <script>
        (function () {
            const form = document.getElementById('membership-form');
            if (!form) return;
            const provinces = @json($provinces);

            // Province -> district -> municipality
            const province = document.getElementById('province');
            const district = document.getElementById('district');
            const municipality = document.getElementById('municipality');
            const fill = (select, names, selected, emptyText) => {
                select.innerHTML = '<option value="">' + (names.length ? 'Select…' : emptyText) + '</option>';
                names.forEach((name) => select.add(new Option(name, name, false, name === selected)));
            };
            const fillDistricts = (selected) => fill(district, Object.keys(provinces[province.value] || {}), selected, 'Select province first');
            const fillMunicipalities = (selected) => fill(municipality, (provinces[province.value] || {})[district.value] || [], selected, 'Select district first');
            fillDistricts(district.dataset.selected);
            fillMunicipalities(municipality.dataset.selected);
            province.addEventListener('change', () => { fillDistricts(''); fillMunicipalities(''); });
            district.addEventListener('change', () => fillMunicipalities(''));

            // Same as permanent address
            const permanent = document.getElementById('permanent_address');
            const current = document.getElementById('current_address');
            const same = document.getElementById('same-address');
            const syncAddress = () => { if (same.checked) current.value = permanent.value; current.readOnly = same.checked; };
            same.addEventListener('change', syncAddress);
            permanent.addEventListener('input', syncAddress);

            // Image previews
            form.querySelectorAll('[data-image-input]').forEach((input) => {
                input.addEventListener('change', () => {
                    const preview = form.querySelector('[data-preview-for="' + input.name + '"]');
                    const file = input.files && input.files[0];
                    if (!preview || !file) return;
                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('hidden');
                });
            });

            // Voucher is needed only for types that have a fee
            const feeNote = document.getElementById('voucher-fee');
            const star = document.getElementById('voucher-required');
            const updateFee = () => {
                const chosen = form.querySelector('input[name="membership_type_id"]:checked');
                const fee = chosen ? chosen.dataset.fee : '';
                feeNote.textContent = fee ? 'Fee for this membership: ' + fee + '. Voucher required.' : '';
                feeNote.classList.toggle('hidden', !fee);
                star.classList.toggle('hidden', !fee);
            };
            form.querySelectorAll('input[name="membership_type_id"]').forEach((radio) => radio.addEventListener('change', updateFee));
            updateFee();

            form.addEventListener('submit', () => {
                const button = document.getElementById('membership-submit');
                button.disabled = true;
                button.textContent = 'Submitting…';
            });
        })();
    </script>
    @endpush
</x-layouts.dashboard>
