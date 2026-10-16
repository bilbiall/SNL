@extends('layouts.site')

@section('title', 'Snakes & Liquor — the party board game')

@section('body')
<div class="snl-wrap">
    <header class="flex items-center justify-between py-3">
        <a href="{{ route('home') }}" class="logo text-xl">🐍 Snakes <b>&amp;</b> Liquor 🥃</a>
        <a href="{{ route('join') }}" class="btn btn-ghost btn-sm">Join a room</a>
    </header>

    @if($errors->any())
        <div class="snl-alert err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <section class="text-center pt-8 pb-8">
        <p class="snl-muted font-extrabold text-xs uppercase tracking-widest mb-3">The party board game</p>
        <h1 class="snl-h text-4xl sm:text-6xl leading-tight mb-4">Every square<br>has a <span style="background:linear-gradient(135deg,#f59e0b,#ec4899);-webkit-background-clip:text;background-clip:text;color:transparent;">story</span>.</h1>
        <p class="snl-muted max-w-xl mx-auto text-base sm:text-lg mb-8">Roll the dice, dodge the snakes, and find out who has to take the shot, pick a friend, state a fact… or break a rule. One phone on the table, or everybody online.</p>

        <div class="grid sm:grid-cols-2 gap-4 max-w-2xl mx-auto text-left">
            <a href="{{ route('setup', ['type' => 'party']) }}" class="snl-card block hover:brightness-110 transition" style="text-decoration:none;color:inherit;">
                <div class="text-4xl mb-2">📱</div>
                <h2 class="snl-h text-xl mb-1">Party mode</h2>
                <p class="snl-muted text-sm mb-4">One device, everyone around it. Add 2–12 players, pass the phone, play.</p>
                <span class="btn btn-primary btn-block">Start a party game</span>
            </a>
            <a href="{{ route('setup', ['type' => 'online']) }}" class="snl-card block hover:brightness-110 transition" style="text-decoration:none;color:inherit;">
                <div class="text-4xl mb-2">🌍</div>
                <h2 class="snl-h text-xl mb-1">Play online</h2>
                <p class="snl-muted text-sm mb-4">Everyone on their own phone. Share a 5-letter code — no sign-up needed.</p>
                <span class="btn btn-violet btn-block">Create a room</span>
            </a>
        </div>

        <form action="{{ route('join') }}" method="GET" class="flex gap-2 max-w-sm mx-auto mt-6">
            <input name="code" class="snl-input" placeholder="Got a code?  e.g. K7QMX" maxlength="8" autocomplete="off" style="text-transform:uppercase;letter-spacing:.2em;text-align:center;font-weight:800;">
            <button class="btn btn-ghost">Join</button>
        </form>
    </section>

    <section class="grid sm:grid-cols-3 gap-3 mt-4">
        <div class="snl-card"><div class="text-2xl mb-1">🎲</div><h3 class="font-black mb-1">Roll &amp; race</h3><p class="snl-muted text-sm">First to the finish wins. Ladders lift you, snakes drag you back, and exact finishes bounce.</p></div>
        <div class="snl-card"><div class="text-2xl mb-1">🃏</div><h3 class="font-black mb-1">Land on a card</h3><p class="snl-muted text-sm">Shots, pick-a-friend, duels, swaps, fun facts and table rules. The roller chooses who it hits.</p></div>
        <div class="snl-card"><div class="text-2xl mb-1">🛡️</div><h3 class="font-black mb-1">Fight back</h3><p class="snl-muted text-sm">Win Shields to block a hit and Rerolls to dodge a bad landing. Use them wisely.</p></div>
    </section>

    <section class="snl-card mt-4">
        <h3 class="font-black mb-3">Pick your vibe</h3>
        <div class="grid sm:grid-cols-3 gap-3">
            @foreach(config('snl.modes') as $key => $m)
            <div class="rounded-2xl p-4" style="background:rgba(255,255,255,.04);border:1px solid var(--line);{{ $m['enabled'] ? '' : 'opacity:.5;' }}">
                <div class="text-2xl">{{ $m['emoji'] }}</div>
                <div class="font-black mt-1">{{ $m['label'] }}@if($m['adult']) <span class="text-xs" style="color:#fbbf24;">18+</span>@endif @unless($m['enabled']) <span class="text-xs snl-muted">soon</span>@endunless</div>
                <p class="snl-muted text-xs mt-1">{{ $m['blurb'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <footer class="snl-foot">
        🍹 Drinks mode is for adults only. Please drink responsibly, never drive after drinking, and swap any drink for water whenever you like.<br>
        <a href="{{ route('login') }}">Admin</a>
    </footer>
</div>
@endsection
