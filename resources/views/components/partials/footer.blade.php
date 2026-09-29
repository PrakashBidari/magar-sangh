<footer class="bg-navy text-white">
    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 py-12 md:grid-cols-4">
        <div>
            <div class="flex items-center gap-3">
                @if ($siteSettings->logo_url)
                <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->site_name_en }}" class="h-16 w-auto rounded-md bg-white object-contain p-1">
                @endif
                <div>
                    <div class="np text-lg font-bold text-white">{{ $siteSettings->site_name_np }}</div>
                    <div class="font-heading text-xs font-semibold uppercase text-white">{{ $siteSettings->site_name_en }}</div>
                </div>
            </div>
            <p class="np mt-4 text-sm text-white">{{ $siteSettings->tagline_np }}</p>
            <p class="mt-1 text-xs text-white">{{ $siteSettings->tagline_en }}</p>
        </div>

        <div>
            <h3 class="font-heading mb-4 text-sm font-bold uppercase tracking-wider text-white">Contact Us</h3>
            <ul class="space-y-2 text-sm text-white">
                <li class="flex gap-2"><i class="fa-solid fa-location-dot mt-1 w-4 text-white"></i><span>{{ $siteSettings->address_en }}</span></li>
                <li class="flex gap-2"><i class="fa-solid fa-phone mt-1 w-4 text-white"></i><span>{{ $siteSettings->phone }}</span></li>
                <li class="flex gap-2"><i class="fa-solid fa-envelope mt-1 w-4 text-white"></i><span>{{ $siteSettings->email }}</span></li>
                <li class="flex gap-2"><i class="fa-solid fa-globe mt-1 w-4 text-white"></i><span>www.nepalmagar.org.np</span></li>
            </ul>
        </div>

        <div>
            <h3 class="font-heading mb-4 text-sm font-bold uppercase tracking-wider text-white">Follow Us</h3>
            <ul class="space-y-3 text-sm text-white">
                @if($siteSettings->facebook_url)
                <li><a href="{{ $siteSettings->facebook_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-facebook w-5 text-lg text-white"></i>Facebook</a></li>
                @endif
                @if($siteSettings->instagram_url)
                <li><a href="{{ $siteSettings->instagram_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-instagram w-5 text-lg text-white"></i>Instagram</a></li>
                @endif
                @if($siteSettings->youtube_url)
                <li><a href="{{ $siteSettings->youtube_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-youtube w-5 text-lg text-white"></i>YouTube</a></li>
                @endif
                @if($siteSettings->twitter_url)
                <li><a href="{{ $siteSettings->twitter_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-x-twitter w-5 text-lg text-white"></i>Twitter</a></li>
                @endif
                @if($siteSettings->tiktok_url)
                <li><a href="{{ $siteSettings->tiktok_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-white"><i class="fa-brands fa-tiktok w-5 text-lg text-white"></i>TikTok</a></li>
                @endif
            </ul>
        </div>

        <div>
            <h3 class="font-heading mb-4 text-sm font-bold uppercase tracking-wider text-white">Our Location</h3>
            @if ($siteSettings->map_embed_url)
            <div class="overflow-hidden rounded-lg border border-white/30">
                <iframe src="{{ $siteSettings->map_embed_url }}" class="h-40 w-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Our Location"></iframe>
            </div>
            @endif
        </div>
    </div>

    <div class="border-t border-white/30">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-xs text-white md:flex-row">
            <p>&copy; {{ date('Y') }} {{ $siteSettings->site_name_en }}. All Rights Reserved.</p>
            <p>{{ $siteSettings->footer_credit }}</p>
        </div>
    </div>
</footer>
