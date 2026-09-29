@props(['title' => null])
<!DOCTYPE html>
<html lang="ne" data-lang="np">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ? $title.' - ' : '' }}{{ $siteSettings->site_name_en }}</title>
    <meta name="description" content="{{ $siteSettings->about_short_en }}">
    @if ($siteSettings->logo_url)<link rel="icon" href="{{ $siteSettings->logo_url }}">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col bg-navy-50 text-gray-800">
    <x-partials.topbar />
    <x-partials.header />
    <x-partials.nav />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-partials.footer />

    <div id="google_translate_element"></div>

    @livewireScripts
</body>
</html>
