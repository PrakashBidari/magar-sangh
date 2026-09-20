<footer class="bg-navy text-gray-200">
    <div class="h-20 w-full bg-cover bg-center opacity-40" style="background-image: url('https://picsum.photos/seed/himalaya-range/1600/200')"></div>

    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 py-12 md:grid-cols-4">
        <div>
            <div class="flex items-center gap-3">
                <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->site_name_en }}" class="h-14 w-14 rounded-full object-cover">
                <div>
                    <div class="np text-lg font-bold text-white">{{ $siteSettings->site_name_np }}</div>
                    <div class="font-heading text-xs font-semibold uppercase text-gold-300">{{ $siteSettings->site_name_en }}</div>
                </div>
            </div>
            <p class="np mt-4 text-sm text-gray-300">{{ $siteSettings->tagline_np }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $siteSettings->tagline_en }}</p>
        </div>

        <div>
            <h3 class="font-heading mb-4 text-sm font-bold uppercase tracking-wider text-gold-300">Contact Us</h3>
            <ul class="space-y-2 text-sm text-gray-300">
                <li class="flex gap-2"><i class="fa-solid fa-location-dot mt-1 w-4 text-gold-300"></i><span>{{ $siteSettings->address_en }}</span></li>
                <li class="flex gap-2"><i class="fa-solid fa-phone mt-1 w-4 text-gold-300"></i><span>{{ $siteSettings->phone }}</span></li>
                <li class="flex gap-2"><i class="fa-solid fa-envelope mt-1 w-4 text-gold-300"></i><span>{{ $siteSettings->email }}</span></li>
                <li class="flex gap-2"><i class="fa-solid fa-globe mt-1 w-4 text-gold-300"></i><span>www.nepalmagar.org.np</span></li>
            </ul>
        </div>

        <div>
            <h3 class="font-heading mb-4 text-sm font-bold uppercase tracking-wider text-gold-300">Follow Us</h3>
            <ul class="space-y-3 text-sm text-gray-300">
                @if($siteSettings->facebook_url)
                <li><a href="{{ $siteSettings->facebook_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-facebook w-5 text-lg text-gold-300"></i>Facebook</a></li>
                @endif
                @if($siteSettings->instagram_url)
                <li><a href="{{ $siteSettings->instagram_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-instagram w-5 text-lg text-gold-300"></i>Instagram</a></li>
                @endif
                @if($siteSettings->youtube_url)
                <li><a href="{{ $siteSettings->youtube_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-youtube w-5 text-lg text-gold-300"></i>YouTube</a></li>
                @endif
                @if($siteSettings->twitter_url)
                <li><a href="{{ $siteSettings->twitter_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-x-twitter w-5 text-lg text-gold-300"></i>Twitter</a></li>
                @endif
                @if($siteSettings->tiktok_url)
                <li><a href="{{ $siteSettings->tiktok_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-tiktok w-5 text-lg text-gold-300"></i>TikTok</a></li>
                @endif
            </ul>
        </div>

        <div>
            <h3 class="font-heading mb-4 text-sm font-bold uppercase tracking-wider text-gold-300">Our Location</h3>
            <div class="overflow-hidden rounded-lg border border-white/10">
                <iframe src="{{ $siteSettings->map_embed_url }}" class="h-40 w-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Our Location"></iframe>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-xs text-gray-400 md:flex-row">
            <p>&copy; {{ date('Y') }} {{ $siteSettings->site_name_en }}. All Rights Reserved.</p>
            <p>{{ $siteSettings->footer_credit }}</p>
        </div>
    </div>
</footer>
