<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Ziggy routes (WAJIB untuk route() di React) --}}
    @routes

    {{-- Inline script: deteksi dark mode system sebelum React render --}}
    <script>
        (function () {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    {{-- Inline background agar tidak white flash --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    {{-- TITLE (INERTIA AMAN) --}}
    <title inertia>{{ config('app.name', 'IKA UNIMED') }}</title>

    {{-- ===== SEO DEFAULT (AMAN, SERVER-SIDE) ===== --}}
    <meta name="description"
          content="{{ $meta_description ?? 'Informasi terbaru seputar kegiatan dan alumni UNIMED.' }}">

    <meta name="author"
          content="{{ $meta_author ?? 'IKA UNIMED' }}">

    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $og_title ?? config('app.name') }}">
    <meta property="og:description" content="{{ $og_description ?? '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image"
          content="{{ $og_image ?? asset('images/cta_ikaunimed-002.png') }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $og_title ?? config('app.name') }}">
    <meta name="twitter:description" content="{{ $og_description ?? '' }}">
    <meta name="twitter:image"
          content="{{ $og_image ?? asset('images/cta_ikaunimed-002.png') }}">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/favicon_ikaunimed.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/favicon_ikaunimed.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    {{-- Google AdSense Global Script (dikontrol dari Site Settings) --}}
    @php
        $adsEnabled = \App\Models\SiteSetting::getValue('ads_enabled', false);
        $adsProvider = \App\Models\SiteSetting::getValue('ads_provider_primary', 'adsense');
        $adsenseClientId = \App\Models\SiteSetting::getValue('adsense_client_id', 'ca-pub-xxxxxxxxxxxxxxxx');
    @endphp
    @if($adsEnabled && $adsProvider === 'adsense')
        <script
            async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseClientId }}"
            crossorigin="anonymous">
        </script>
    @endif

    {{-- Vite + Inertia --}}
    @viteReactRefresh
    @vite('resources/js/app.tsx')
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia

    {{-- Adsterra Global Snippet (opsional, dikontrol dari Site Settings) --}}
    @php
        $adsterraEnabled = \App\Models\SiteSetting::getValue('adsterra_enabled', false);
        $adsterraBodySnippet = \App\Models\SiteSetting::getValue('adsterra_script_body', '');
    @endphp
    @if($adsterraEnabled && !empty($adsterraBodySnippet))
        {!! $adsterraBodySnippet !!}
    @endif
</body>
</html>
