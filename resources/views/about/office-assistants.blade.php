<x-layouts.app title="Office Assistant">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <div class="mb-10 text-center">
            <h1 class="section-title-np">कार्यालय सहायक</h1>
            <p class="section-title-en mt-1">Office Assistant</p>
        </div>

        @if ($assistants->isEmpty())
        <p class="np mt-10 text-center text-gray-500">कार्यालय सहायकको विवरण उपलब्ध छैन।</p>
        @else
        <div class="flex flex-wrap justify-center gap-6">
            @foreach ($assistants as $assistant)
            <div class="card w-full max-w-xs border-t-4 border-maroon text-center sm:w-72">
                <img src="{{ $assistant->photo_url }}" alt="{{ $assistant->name }}" class="glightbox-img mx-auto h-36 w-36 rounded-full border-4 border-white object-cover shadow-md" data-gallery="office-assistants">
                <h2 class="np mt-4 text-lg font-bold text-navy">{{ $assistant->name }}</h2>
                <div class="mt-3 space-y-1.5 text-sm">
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $assistant->phone) }}" class="flex items-center justify-center gap-2 font-semibold text-gray-700 hover:text-maroon">
                        <i class="fa-solid fa-phone text-maroon"></i> {{ $assistant->phone }}
                    </a>
                    @if ($assistant->email)
                    <a href="mailto:{{ $assistant->email }}" class="flex items-center justify-center gap-2 break-all text-gray-600 hover:text-maroon">
                        <i class="fa-solid fa-envelope text-maroon"></i> {{ $assistant->email }}
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>
</x-layouts.app>
