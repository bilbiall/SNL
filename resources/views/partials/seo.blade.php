@php
    $seoTitle = trim($__env->yieldContent('title', 'Snakes & Liquor'));
    $seoDesc = trim($__env->yieldContent('meta_description')) ?: 'Snakes & Liquor is a free party board game: roll, land and play trivia, bluffing, quickfire challenges and group games. Family mode for everyone, an optional adults-only mode, works offline on your phone.';
    $seoCanon = trim($__env->yieldContent('canonical')) ?: url()->current();
    $seoRobots = trim($__env->yieldContent('robots')) ?: 'index,follow,max-image-preview:large';
    $seoImage = asset('img/og-image.png');
@endphp
<meta name="description" content="{{ $seoDesc }}">
<link rel="canonical" href="{{ $seoCanon }}">
<meta name="robots" content="{{ $seoRobots }}">
<meta name="keywords" content="party board game, snakes and ladders game online, trivia party game, bluffing game, group games for parties, family game night, offline party game app, quickfire challenges, icebreaker games">
<meta property="og:site_name" content="Snakes & Liquor">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDesc }}">
<meta property="og:url" content="{{ $seoCanon }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Snakes &amp; Liquor — the party board game where every square has a story">
<meta property="og:locale" content="en">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDesc }}">
<meta name="twitter:image" content="{{ $seoImage }}">
@stack('jsonld')
