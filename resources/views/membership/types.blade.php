@php
    $applyUrl = fn ($type = null) => route('dashboard.my-membership.create', $type ? ['type' => $type->id] : []);
@endphp
<x-layouts.app title="Membership">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">सदस्यता</h1>
        <p class="section-title-en mt-1">Membership</p>

        <p class="mt-6 max-w-3xl text-gray-700">
            Individuals who wish to become affiliated with the Nepal Magar Association and contribute to the unity, identity, rights, culture, and social development of the Magar community may apply by selecting the appropriate membership category.
        </p>

        @guest
        <div class="mt-6 flex flex-wrap items-center gap-3 rounded-lg border-l-4 border-gold bg-gold-50 px-4 py-3 text-sm text-gray-700">
            <span>Please log in or create an account to apply for membership.</span>
            <a href="{{ route('login') }}" class="font-semibold text-maroon hover:underline">Login</a>
            <a href="{{ route('register') }}" class="font-semibold text-maroon hover:underline">Register</a>
        </div>
        @endguest

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($types as $type)
            <div class="card flex flex-col border-t-4 border-maroon">
                <h2 class="text-lg font-bold text-navy">{{ $type->name_en }}</h2>
                <div class="np text-sm text-gray-500">{{ $type->name_np }}</div>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-navy-50 px-3 py-1 text-navy">⏱ {{ $type->durationLabel() }}</span>
                    <span class="rounded-full px-3 py-1 {{ $type->requiresPayment() ? 'bg-gold-100 text-gold-700' : 'bg-green-100 text-green-700' }}">{{ $type->fee_label }}</span>
                </div>
                @if ($type->description)
                <p class="mt-3 flex-1 text-sm text-gray-600">{{ $type->description }}</p>
                @else
                <div class="flex-1"></div>
                @endif
                <a href="{{ $applyUrl($type) }}" class="btn-maroon mt-5 justify-center">Apply now</a>
            </div>
            @empty
            <p class="text-gray-500">Membership applications are not open right now.</p>
            @endforelse
        </div>
    </section>
</x-layouts.app>
