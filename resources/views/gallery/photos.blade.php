<x-layouts.app title="Photo Gallery">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">फोटो ग्यालरी</h1>
        <p class="section-title-en mt-1">Photo Gallery</p>

        <div class="mt-10 columns-2 gap-4 sm:columns-3 lg:columns-4 [&>*]:mb-4">
            @foreach ($photos as $photo)
            <a href="{{ $photo->image_url }}" class="glightbox-img block overflow-hidden rounded-lg shadow hover:opacity-90" data-gallery="photo-gallery" data-title="{{ $photo->title }}">
                <img src="{{ $photo->image_url }}" alt="{{ $photo->title }}" class="w-full object-cover">
            </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $photos->links() }}
        </div>
    </section>
</x-layouts.app>
