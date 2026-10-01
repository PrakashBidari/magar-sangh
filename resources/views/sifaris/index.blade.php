<x-layouts.app title="Sifaris">
    <section class="mx-auto max-w-7xl px-4 py-14">
        <h1 class="section-title-np">सिफारिस</h1>
        <p class="section-title-en mt-1">Sifaris (Recommendation Letter)</p>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <p class="text-gray-700">
                    The Nepal Magar Association issues a sifaris (recommendation letter) confirming that the applicant is Magar by descent. It is addressed to the National Foundation for Development of Indigenous Nationalities (आदिवासी जनजाति उत्थान राष्ट्रिय प्रतिष्ठान), Sanepa, Lalitpur.
                </p>

                <h2 class="mt-8 text-lg font-bold text-navy">What you need</h2>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-gray-700">
                    <li>Your full name, and the names of your father (or husband) and grandfather (or father-in-law)</li>
                    <li>Your permanent address: province, district, municipality / rural municipality and ward</li>
                    <li>A recent passport-size photo</li>
                    <li>A mobile number the office can reach you on</li>
                </ul>

                <h2 class="mt-8 text-lg font-bold text-navy">How it works</h2>
                <ol class="mt-3 list-decimal space-y-1 pl-5 text-gray-700">
                    <li>Log in and fill in the sifaris form in your dashboard.</li>
                    <li>The office checks your details and approves the request.</li>
                    <li>Download the signed letter from your dashboard as a PDF or image.</li>
                </ol>

                @guest
                <div class="mt-8 flex flex-wrap items-center gap-3 rounded-lg border-l-4 border-gold bg-gold-50 px-4 py-3 text-sm text-gray-700">
                    <span>Please log in or create an account to apply for a sifaris.</span>
                    <a href="{{ route('login') }}" class="font-semibold text-maroon hover:underline">Login</a>
                    <a href="{{ route('register') }}" class="font-semibold text-maroon hover:underline">Register</a>
                </div>
                @endguest

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard.my-sifaris.create') }}" class="btn-maroon justify-center">Apply for sifaris</a>
                    @auth
                    <a href="{{ route('dashboard.my-sifaris.index') }}" class="rounded-md border border-gray-300 px-6 py-3 text-sm font-semibold text-navy hover:bg-gray-50">My sifaris requests</a>
                    @endauth
                </div>
            </div>

            <div class="lg:col-span-2">
                <img src="{{ asset('images/sifaris-template.jpg') }}" alt="Sample sifaris letter" loading="lazy" class="mx-auto w-full max-w-sm rounded-md shadow-xl ring-1 ring-gray-200">
            </div>
        </div>
    </section>
</x-layouts.app>
