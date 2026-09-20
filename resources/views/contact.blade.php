<x-layouts.app title="Contact Us">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">सम्पर्क गर्नुहोस्</h1>
        <p class="section-title-en mt-1">Contact Us</p>

        <div class="mt-10 grid grid-cols-1 gap-10 md:grid-cols-2">
            <div class="card">
                <livewire:contact-form />
            </div>

            <div class="space-y-6">
                <div class="card">
                    <h2 class="text-lg font-bold text-navy">Reach Us</h2>
                    <ul class="mt-4 space-y-3 text-sm text-gray-700">
                        <li class="flex gap-2"><span>📍</span><span>{{ $settings->address_en }}</span></li>
                        <li class="flex gap-2"><span>📞</span><span>{{ $settings->phone }}</span></li>
                        <li class="flex gap-2"><span>✉️</span><span>{{ $settings->email }}</span></li>
                    </ul>
                    <div class="mt-4 flex gap-3 text-navy">
                        @if($settings->facebook_url)<a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener" class="hover:text-maroon">Facebook</a>@endif
                        @if($settings->instagram_url)<a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener" class="hover:text-maroon">Instagram</a>@endif
                        @if($settings->youtube_url)<a href="{{ $settings->youtube_url }}" target="_blank" rel="noopener" class="hover:text-maroon">YouTube</a>@endif
                    </div>
                </div>

                <div class="card overflow-hidden p-0">
                    <iframe src="{{ $settings->map_embed_url }}" class="h-64 w-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Our Location"></iframe>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
