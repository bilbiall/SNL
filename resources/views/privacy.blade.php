@extends('layouts.site')

@section('title', 'Privacy policy | Snakes & Liquor')
@section('meta_description', 'What Snakes & Liquor stores, what it does not, how cookies and Google ads and analytics work, and how to reach us.')

@push('head')
<style>
    body { background: radial-gradient(ellipse at 50% 0%, #2a1550, #0b0a16 62%); min-height: 100vh; }
    .doc { max-width: 46rem; margin: 0 auto; padding: 1rem 1.1rem 3.5rem; line-height: 1.6; }
    .doc h1 { font-family: 'Bungee', 'Figtree', sans-serif; font-weight: 400; font-size: clamp(1.6rem, 6vw, 2.4rem); line-height: 1.1; margin: 1.2rem 0 .5rem; }
    .doc h2 { font-size: 1.15rem; font-weight: 900; margin: 2rem 0 .5rem; color: #fbbf24; }
    .doc p, .doc li { color: #dbe3ee; font-weight: 500; } .doc a { color: #f9a8d4; }
    .doc ul { padding-left: 1.2rem; list-style: disc; } .doc li { margin: .3rem 0; }
    .doc .upd { color: #94a3b8; font-size: .85rem; }
</style>
@endpush

@section('body')
<div class="doc">
    <x-brand />
    <h1>Privacy policy</h1>
    <p class="upd">Last updated {{ $updated }}</p>
    <p>Snakes &amp; Liquor is a free party board game. There are no accounts, and we collect as little as we can. This page explains what is stored and why.</p>

    <h2>What you give us</h2>
    <ul>
        <li><b>Player names, avatars and colours</b> you type or pick when you start or join a game. Use a nickname if you prefer — real names are never required.</li>
        <li><b>Room details</b>: the room code, its settings, the moves and cards played, and (if the host makes the room public) the room name shown in the public list.</li>
        <li><b>Reactions</b> you send to the table during an online game.</li>
    </ul>
    <p>We do not ask for your email, phone number, location or payment details to play. Online rooms and the players in them are deleted automatically a few days after the last activity.</p>

    <h2>What stays on your device</h2>
    <p>Party mode runs in your browser or app. Your saved game, sound and music settings and offline game files are kept in your device's local storage so the game works without a signal. Clearing your browser data removes them. We never receive them.</p>

    <h2>Cookies and similar technology</h2>
    <ul>
        <li><b>Essential:</b> a session cookie that keeps you in your online room and protects forms from forgery. The game cannot work without it.</li>
        <li><b>Analytics:</b> if switched on, Google Analytics counts visits and game starts and wins (with IP addresses anonymised). It is never loaded inside the Android or Windows apps.</li>
        <li><b>Advertising:</b> if switched on, Google AdSense may show an ad when a host starts a new game or taps Play again — never during a game. Google and its partners may use cookies or similar technology to show and measure ads, and may personalise them based on your visits to this and other sites. You can manage this at <a href="https://adssettings.google.com" target="_blank" rel="noopener">adssettings.google.com</a>, and learn more at <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">how Google uses data from sites that use its services</a>. The apps do not show web ads.</li>
    </ul>

    <h2>Who we share data with</h2>
    <p>We do not sell your data. The services above (Google Analytics and Google AdSense) receive technical information such as your IP address and browser details when they are active. Our hosting provider processes requests to serve the site.</p>

    <h2>Age</h2>
    <p>Family mode is for everyone. The adults-only mode and any drinking-related content are for people <b>18 and over</b> and for those of legal drinking age where they live. Please drink responsibly and never drive after drinking. Children under 13 should play with a parent or guardian.</p>

    <h2>Your choices</h2>
    <ul>
        <li>Leave a room at any time from the menu, and clear your browser data to remove local saves.</li>
        <li>Block cookies in your browser; party mode still works, though online rooms need the session cookie.</li>
        <li>Ask us to delete anything tied to a room code, using the contact below.</li>
    </ul>

    <h2>Changes</h2>
    <p>If this policy changes, the date above is updated.</p>

    <h2>Contact</h2>
    @if($email)
        <p>Questions or requests: <a href="mailto:{{ $email }}">{{ $email }}</a>. See also <a href="{{ route('about') }}">About</a>.</p>
    @else
        <p>See the <a href="{{ route('about') }}">About page</a> for how to reach us.</p>
    @endif
</div>
@endsection
