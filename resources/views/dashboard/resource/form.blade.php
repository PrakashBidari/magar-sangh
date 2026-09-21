@php
    $editing = (bool) $model;
    $action = $editing
        ? route('dashboard.'.$key.'.update', $model->getKey())
        : route('dashboard.'.$key.'.store');
    $title = ($editing ? 'Edit ' : 'Add ').$cfg['singular'];
    $input = 'mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon';
@endphp
<x-layouts.dashboard :title="$title">
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-navy">{{ $cfg['icon'] }} {{ $title }}</h2>
            <a href="{{ route('dashboard.'.$key.'.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to {{ $cfg['label'] }}</a>
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

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="resource-form" class="card grid grid-cols-1 gap-x-5 gap-y-5 sm:grid-cols-2">
            @csrf
            @if ($editing) @method('PUT') @endif

            @foreach ($cfg['fields'] as $field)
                @php
                    $name = $field['name'];
                    $type = $field['type'];
                    $value = old($name, $values[$name] ?? null);
                    $span = ($field['width'] ?? 'full') === 'half' ? '' : 'sm:col-span-2';
                    $extra = $field['class'] ?? '';
                @endphp
                <div class="{{ $span }} min-w-0">
                    @if ($type !== 'checkbox')
                    <label for="f-{{ $name }}" class="text-sm font-semibold text-gray-700">
                        {{ $field['label'] }}
                        @if (str_contains($field['rules'] ?? '', 'required') || (($field['required_on_create'] ?? false) && ! $editing)) <span class="text-red-500">*</span> @endif
                    </label>
                    @endif

                    @switch($type)
                        @case('textarea')
                            <textarea id="f-{{ $name }}" name="{{ $name }}" rows="3" class="{{ $input }} {{ $extra }}">{{ $value }}</textarea>
                            @break

                        @case('richtext')
                            <div class="mt-1">
                                <textarea id="f-{{ $name }}" name="{{ $name }}" class="js-rich">{{ $value }}</textarea>
                            </div>
                            @break

                        @case('select')
                            <select id="f-{{ $name }}" name="{{ $name }}" class="{{ $input }}">
                                @foreach ($field['options'] as $optValue => $optLabel)
                                <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ $optLabel }}</option>
                                @endforeach
                            </select>
                            @break

                        @case('checkbox')
                            <label class="flex items-center gap-2 pt-2 text-sm font-semibold text-gray-700">
                                <input type="hidden" name="{{ $name }}" value="0">
                                <input type="checkbox" id="f-{{ $name }}" name="{{ $name }}" value="1" @checked($value) class="rounded border-gray-300 text-maroon focus:ring-maroon">
                                {{ $field['label'] }}
                            </label>
                            @break

                        @case('image')
                            <div class="mt-1 flex flex-wrap items-start gap-4">
                                <img data-preview-for="f-{{ $name }}" src="{{ $value ?: '' }}" alt="" class="h-24 w-24 rounded-md border border-gray-200 bg-gray-50 object-cover {{ $value ? '' : 'hidden' }}">
                                <div class="min-w-0 flex-1">
                                    <input type="file" id="f-{{ $name }}" name="{{ $name }}" accept="image/*" data-image-input class="w-full text-sm">
                                    <p class="mt-1 text-xs text-gray-400">JPG, PNG, WEBP or GIF, up to {{ round(($field['max'] ?? 4096) / 1024) }} MB. Choose a file from your computer.</p>
                                    @if ($editing && $value && ! ($field['required_on_create'] ?? false))
                                    <label class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                        <input type="checkbox" name="remove_{{ $name }}" value="1" class="rounded border-gray-300 text-maroon focus:ring-maroon"> Remove current image
                                    </label>
                                    @endif
                                </div>
                            </div>
                            @break

                        @case('file')
                            <div class="mt-1">
                                <input type="file" id="f-{{ $name }}" name="{{ $name }}" class="w-full text-sm">
                                <p class="mt-1 text-xs text-gray-400">Allowed: {{ strtoupper(str_replace(',', ', ', $field['mimes'] ?? 'any')) }} — up to {{ round(($field['max'] ?? 10240) / 1024) }} MB.</p>
                                @if ($editing && $value)
                                <p class="mt-1 text-xs">Current file: <a href="{{ $value }}" target="_blank" rel="noopener" class="font-semibold text-navy hover:underline">open</a></p>
                                @endif
                            </div>
                            @break

                        @case('password')
                            <input type="password" id="f-{{ $name }}" name="{{ $name }}" autocomplete="new-password" class="{{ $input }}">
                            @break

                        @default
                            @php
                                $htmlType = match ($type) { 'datetime' => 'datetime-local', 'url' => 'url', default => $type };
                            @endphp
                            <input type="{{ $htmlType }}" id="f-{{ $name }}" name="{{ $name }}" value="{{ $value }}"
                                @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                                @if (isset($suggestions[$name])) list="dl-{{ $name }}" @endif
                                class="{{ $input }} {{ $extra }}">
                            @if (isset($suggestions[$name]))
                            <datalist id="dl-{{ $name }}">
                                @foreach ($suggestions[$name] as $option)
                                <option value="{{ $option }}"></option>
                                @endforeach
                            </datalist>
                            @endif
                    @endswitch

                    @if (! empty($field['help']))
                    <p class="mt-1 text-xs text-gray-400">{{ $field['help'] }}</p>
                    @endif
                    @error($name)
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="flex flex-col-reverse gap-3 border-t pt-5 sm:col-span-2 sm:flex-row sm:justify-end">
                <a href="{{ route('dashboard.'.$key.'.index') }}" class="rounded-md border border-gray-300 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" id="resource-submit" class="btn-maroon justify-center">{{ $editing ? 'Save Changes' : 'Create '.$cfg['singular'] }}</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            const form = document.getElementById('resource-form');
            const editors = [];

            form.querySelectorAll('.js-rich').forEach((textarea) => {
                NepalRichEditor.init(textarea)
                    .then((editor) => editors.push(editor))
                    .catch(() => {});
            });

            // Live preview for chosen images
            form.querySelectorAll('[data-image-input]').forEach((input) => {
                input.addEventListener('change', () => {
                    const preview = form.querySelector('[data-preview-for="' + input.id + '"]');
                    const file = input.files && input.files[0];
                    if (!preview || !file) return;
                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('hidden');
                });
            });

            form.addEventListener('submit', () => {
                editors.forEach((editor) => editor.synchronizeValues());
                const button = document.getElementById('resource-submit');
                button.disabled = true;
                button.textContent = 'Saving…';
            });
        })();
    </script>
    @endpush
</x-layouts.dashboard>
