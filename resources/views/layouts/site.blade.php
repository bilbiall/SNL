<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Snakes & Liquor')</title>
    @include('partials.seo')
    @include('partials.analytics')
    @if(preg_match('/^ca-pub-\d+$/', (string) config('snl.ads.client')))<meta name="google-adsense-account" content="{{ config('snl.ads.client') }}">@endif
    @include('partials.pwa-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme')
    @include('partials.brand-css')
    @include('partials.pieces-css')
    @stack('head')
</head>
<body>
    @include('partials.loader')
    @yield('body')
    <script src="{{ \App\Support\OfflineAssets::js('pwa.js') }}"></script>
    <script src="{{ \App\Support\OfflineAssets::js('app-update.js') }}"></script>
</body>
</html>
