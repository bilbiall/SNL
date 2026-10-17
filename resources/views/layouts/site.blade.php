<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Snakes & Liquor')</title>
    <meta name="description" content="The party board game where every square has a story. Play on one phone — even with no signal — or online with friends.">
    @include('partials.pwa-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme')
    @include('partials.brand-css')
    @include('partials.pieces-css')
    @stack('head')
</head>
<body>
    @yield('body')
    <script src="{{ \App\Support\OfflineAssets::js('pwa.js') }}"></script>
</body>
</html>
