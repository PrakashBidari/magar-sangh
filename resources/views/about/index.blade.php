<x-layouts.app title="About Us">
    <section class="mx-auto max-w-5xl px-4 py-14">
        <h1 class="section-title-np">परिचय</h1>
        <p class="section-title-en mt-1">About Nepal Magar Association</p>

        <div id="history" class="card mt-8 scroll-mt-24">
            <h2 class="text-xl font-bold text-navy">Our History</h2>
            <div class="prose prose-sm mt-4 max-w-none text-gray-700">
                {!! $settings->history_content !!}
            </div>
        </div>

        <div id="mission" class="card mt-8 scroll-mt-24">
            <h2 class="text-xl font-bold text-navy">Mission &amp; Vision</h2>
            <div class="prose prose-sm mt-4 max-w-none text-gray-700">
                {!! $settings->mission_vision_content !!}
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <a href="{{ route('about.committee') }}" class="card flex items-center justify-between hover:shadow-lg">
                <span class="font-semibold text-navy">Central Committee</span>
                <span>→</span>
            </a>
            <a href="{{ route('about.constitution') }}" class="card flex items-center justify-between hover:shadow-lg">
                <span class="font-semibold text-navy">Constitution</span>
                <span>→</span>
            </a>
        </div>
    </section>
</x-layouts.app>
