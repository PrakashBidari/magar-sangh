<x-layouts.app title="Constitution">
    <section class="mx-auto max-w-4xl px-4 py-14">
        <h1 class="section-title-np">विधान</h1>
        <p class="section-title-en mt-1">Constitution</p>

        <div class="card prose prose-sm mt-8 max-w-none text-gray-700">
            {!! $settings->constitution_content !!}
        </div>
    </section>
</x-layouts.app>
