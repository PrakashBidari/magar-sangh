@php
    $editing = $transaction->exists;
    $title = $editing ? 'Edit Entry' : 'Add Entry';
    $action = $editing ? route('dashboard.accounting.update', $transaction) : route('dashboard.accounting.store');
    $type = old('type', $transaction->type);
    $category = old('category', $transaction->category);
    $method = old('payment_method', $transaction->payment_method);
    $date = old('date', $transaction->date?->format('Y-m-d'));
    $input = 'mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon';
@endphp
<x-layouts.dashboard :title="$title">
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-navy">📒 {{ $title }}</h2>
            <a href="{{ route('dashboard.accounting.index', ['type' => $type]) }}" class="text-sm font-semibold text-maroon hover:underline">← Back to entries</a>
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

        <form method="POST" action="{{ $action }}" id="entry-form" class="card grid grid-cols-1 gap-x-5 gap-y-5 sm:grid-cols-2">
            @csrf
            @if ($editing) @method('PUT') @endif

            {{-- Type --}}
            <div class="sm:col-span-2">
                <span class="text-sm font-semibold text-gray-700">Type <span class="text-red-500">*</span></span>
                <div class="mt-1 grid grid-cols-2 gap-3">
                    @foreach (['income' => ['Income', '💰', 'peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-700'], 'expense' => ['Expense', '💸', 'peer-checked:border-red-600 peer-checked:bg-red-50 peer-checked:text-red-700']] as $value => [$label, $icon, $checked])
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $value }}" @checked($type === $value) class="peer sr-only">
                        <span class="flex items-center justify-center gap-2 rounded-md border-2 border-gray-200 px-4 py-3 text-sm font-bold text-gray-500 transition peer-focus-visible:ring-2 peer-focus-visible:ring-maroon {{ $checked }}">{{ $icon }} {{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="f-date" class="text-sm font-semibold text-gray-700">Date <span class="text-red-500">*</span></label>
                <input type="date" id="f-date" name="date" value="{{ $date }}" required class="{{ $input }}">
                @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="f-category" class="text-sm font-semibold text-gray-700">Category <span class="text-red-500">*</span></label>
                <select id="f-category" name="category" required class="{{ $input }}">
                    {{-- Filled from the chosen type by the script below; the server-side value is kept for editing --}}
                    @if ($category)<option value="{{ $category }}" selected>{{ $category }}</option>@endif
                </select>
                @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="f-description" class="text-sm font-semibold text-gray-700">Description <span class="text-red-500">*</span></label>
                <input type="text" id="f-description" name="description" value="{{ old('description', $transaction->description) }}" required maxlength="255" placeholder="What was the money received / spent for?" class="{{ $input }}">
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="f-amount" class="text-sm font-semibold text-gray-700">Amount (Rs.) <span class="text-red-500">*</span></label>
                <input type="number" id="f-amount" name="amount" value="{{ old('amount', $transaction->amount) }}" required min="0.01" step="0.01" inputmode="decimal" class="{{ $input }}">
                @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="f-reference_no" class="text-sm font-semibold text-gray-700">Reference No.</label>
                <input type="text" id="f-reference_no" name="reference_no" value="{{ old('reference_no', $transaction->reference_no) }}" maxlength="100" placeholder="Bill / transaction number" class="{{ $input }}">
                @error('reference_no') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Payment method --}}
            <fieldset class="sm:col-span-2">
                <legend class="text-sm font-semibold text-gray-700">Payment method <span class="text-red-500">*</span></legend>
                <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
                    @foreach ($methods as $option)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="payment_method" value="{{ $option }}" @checked($method === $option) required class="border-gray-300 text-maroon focus:ring-maroon">
                        {{ $option }}
                    </label>
                    @endforeach
                </div>
                @error('payment_method') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </fieldset>

            <div class="sm:col-span-2">
                <label for="f-remarks" class="text-sm font-semibold text-gray-700">Remarks</label>
                <textarea id="f-remarks" name="remarks" rows="3" maxlength="1000" class="{{ $input }}">{{ old('remarks', $transaction->remarks) }}</textarea>
                @error('remarks') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 border-t pt-5 sm:col-span-2 sm:flex-row sm:justify-end">
                <a href="{{ route('dashboard.accounting.index', ['type' => $type]) }}" class="rounded-md border border-gray-300 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" id="entry-submit" class="btn-maroon justify-center">{{ $editing ? 'Save Changes' : 'Save Entry' }}</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            const categories = @json($categories);
            const select = document.getElementById('f-category');
            const form = document.getElementById('entry-form');
            let wanted = @json($category);

            // Category choices follow the chosen type; a category no longer in the lists stays selectable while editing.
            function fillCategories() {
                const type = form.querySelector('input[name="type"]:checked')?.value;
                const options = [...(categories[type] || [])];
                if (wanted && !options.includes(wanted) && wanted === @json($transaction->getOriginal('category'))) options.unshift(wanted);
                select.innerHTML = '<option value="">Select category…</option>' + options
                    .map((name) => `<option value="${name.replace(/"/g, '&quot;')}">${name.replace(/&/g, '&amp;').replace(/</g, '&lt;')}</option>`).join('');
                select.value = options.includes(wanted) ? wanted : '';
            }

            form.querySelectorAll('input[name="type"]').forEach((radio) => radio.addEventListener('change', () => {
                wanted = select.value; // keep the choice if the new type offers it too
                fillCategories();
            }));
            fillCategories();

            form.addEventListener('submit', () => {
                const button = document.getElementById('entry-submit');
                button.disabled = true;
                button.textContent = 'Saving…';
            });
        })();
    </script>
    @endpush
</x-layouts.dashboard>
