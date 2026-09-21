<x-layouts.dashboard :title="$cfg['singular'].' Details'">
    <div class="mx-auto max-w-3xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-navy">{{ $cfg['icon'] }} {{ $cfg['singular'] }} Details</h2>
            <a href="{{ route('dashboard.'.$key.'.index') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to {{ $cfg['label'] }}</a>
        </div>

        <div class="card">
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                @foreach ($cfg['fields'] as $field)
                    @php
                        $value = $model->getAttribute($field['name']);
                        $wide = $field['type'] === 'textarea';
                    @endphp
                    <div class="min-w-0 {{ $wide ? 'sm:col-span-2' : '' }}">
                        <dt class="text-xs font-semibold uppercase text-gray-400">{{ $field['label'] }}</dt>
                        <dd class="mt-1 break-words text-sm text-gray-800">
                            @if ($value instanceof \Carbon\Carbon)
                                {{ $value->format('d M, Y h:i A') }}
                            @elseif ($field['name'] === 'email' && $value)
                                <a href="mailto:{{ $value }}" class="font-semibold text-navy hover:underline">{{ $value }}</a>
                            @elseif ($wide)
                                <div class="whitespace-pre-line rounded-md bg-gray-50 p-4">{{ $value }}</div>
                            @else
                                {{ $value ?: '—' }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-6 flex flex-wrap items-center gap-3 border-t pt-4">
                @if ($model->getAttribute('email'))
                <a href="mailto:{{ $model->email }}?subject=Re: {{ $model->subject }}" class="btn-maroon">Reply by Email</a>
                @endif
                <form method="POST" action="{{ route('dashboard.'.$key.'.destroy', $model->getKey()) }}" onsubmit="return confirm('Delete this message? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
