@props(['title' => 'Login'])
<!DOCTYPE html>
<html lang="ne" translate="no" class="notranslate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <title>{{ $title }} - {{ $siteSettings->site_name_en }}</title>
    @if ($siteSettings->logo_url)<link rel="icon" href="{{ $siteSettings->logo_url }}">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-navy px-4 py-10">
    <div class="w-full max-w-md">
        <div class="mb-6 flex flex-col items-center text-center">
            <a href="{{ route('home') }}">
                @if ($siteSettings->logo_url)
                <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->site_name_en }}" class="h-20 w-auto object-contain">
                @endif
            </a>
            <div class="np mt-3 text-xl font-extrabold text-white">{{ $siteSettings->site_name_np }}</div>
            <div class="text-xs font-semibold uppercase tracking-wide text-gold-300">{{ $siteSettings->site_name_en }}</div>
        </div>

        <div class="rounded-lg bg-white p-8 shadow-xl">
            <h1 class="text-lg font-bold text-navy">{{ $title }}</h1>
            <div class="mt-6">
                {{ $slot }}
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-gray-300">
            <a href="{{ route('home') }}" class="hover:text-white">← Back to Website</a>
        </p>
    </div>
</body>
</html>
