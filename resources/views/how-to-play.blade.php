@extends('layouts.site')

@section('title', 'How to play Snakes & Liquor — rules, squares and game modes')
@section('meta_description', 'Learn how to play Snakes & Liquor: roll the dice, land on trivia, bluffing, quickfire and group-game squares, climb ladders and dodge snakes. Family mode for all ages, plays offline.')

@push('jsonld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => 'How to play Snakes & Liquor',
    'description' => 'Race to the finish square on a snakes-and-ladders board while every square asks something of the table.',
    'totalTime' => 'PT30M',
    'step' => [
        ['@type' => 'HowToStep', 'name' => 'Set up', 'text' => 'Pick Family or Adults mode, how lively you want it (Chill, Normal or Spicy), and add the players. One phone or online with friends.'],
        ['@type' => 'HowToStep', 'name' => 'Roll', 'text' => 'On your turn tap the die and your token hops along the board. Ladders lift you up, snakes slide you back.'],
        ['@type' => 'HowToStep', 'name' => 'Land', 'text' => 'Every square asks something: a true-or-false fact, a bluff, a ten-second challenge, a group game or a kind word for someone at the table.'],
        ['@type' => 'HowToStep', 'name' => 'Block', 'text' => 'Use a Block (shield) to cancel one card aimed at you. Win more from power-up squares and free gifts.'],
        ['@type' => 'HowToStep', 'name' => 'Win', 'text' => 'First to the finish square wins. Roll too high and you bounce back.'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('head')
<style>
    body { background: radial-gradient(ellipse at 50% 0%, #2a1550, #0b0a16 62%); min-height: 100vh; }
    .doc { max-width: 46rem; margin: 0 auto; padding: 1rem 1.1rem 3.5rem; line-height: 1.6; }
    .doc h1 { font-family: 'Bungee', 'Figtree', sans-serif; font-weight: 400; font-size: clamp(1.6rem, 6vw, 2.4rem); line-height: 1.1; margin: 1.2rem 0 .5rem; }
    .doc h2 { font-size: 1.15rem; font-weight: 900; margin: 2rem 0 .5rem; color: #fbbf24; }
    .doc p, .doc li { color: #dbe3ee; font-weight: 500; } .doc a { color: #f9a8d4; }
    .doc .lead { font-size: 1.05rem; color: #e2e8f0; }
    .sq { display: grid; grid-template-columns: auto 1fr; gap: .2rem .8rem; padding: .7rem .9rem; margin: .5rem 0; border-radius: 1rem; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); }
    .sq .ic { font-size: 1.7rem; grid-row: span 2; align-self: center; } .sq b { font-size: .95rem; } .sq span { color: #cbd5e1; font-size: .85rem; }
    .doc .cta { display: inline-flex; margin-top: 1rem; padding: .8rem 1.3rem; border-radius: 1rem; font-weight: 900; color: #fff; text-decoration: none; background: linear-gradient(135deg, #f59e0b, #ec4899); }
    .doc .nav2 { display: flex; flex-wrap: wrap; gap: .5rem 1rem; font-size: .85rem; font-weight: 800; margin-top: .6rem; }
</style>
@endpush

@section('body')
<div class="doc">
    <x-brand />
    <h1>How to play Snakes &amp; Liquor</h1>
    <p class="lead">A snakes-and-ladders race where every square asks something of the table — a fact to judge, a bluff to call, a ten-second challenge, a group game. Free to play on one phone (even offline) or online with friends.</p>
    <div class="nav2"><a href="{{ route('home') }}">Play now</a><a href="{{ route('fun-facts') }}">Fun facts quiz</a><a href="{{ route('rooms.index') }}">Public games</a><a href="{{ route('app.page') }}">Get the app</a></div>

    <h2>The goal</h2>
    <p>Be first to reach the finish square. Roll the die, hop along the board, and deal with whatever square you land on. Ladders lift you up, snakes slide you back, and if you roll too high you bounce back off the end.</p>

    <h2>Pick your game</h2>
    <ul>
        @foreach($modes as $m)
            <li><b>{{ $m['label'] }}</b> — {{ $m['blurb'] }}</li>
        @endforeach
    </ul>
    <p>Then choose how lively it gets: @foreach($levels as $l)<b>{{ $l['label'] }}</b>{{ $loop->last ? '' : ($loop->remaining === 1 ? ' or ' : ', ') }}@endforeach. Chill is gentle, Spicy is cheekier — and every card has a fair, light version.</p>

    <h2>What the squares do</h2>
    @foreach($kinds as $k)
        <div class="sq"><x-icon :name="$k['icon']" /><b>{{ $k['label'] }}</b><span>{{ $k['help'] }}</span></div>
    @endforeach
    <p>Some plain-looking free squares hide a surprise too: a <b>Quickfire</b> ten-second challenge, a <b>bonus roll</b>, a free <b>Block</b>, or a <b>group game</b> everyone joins. Others stay calm — a breather is part of the fun.</p>

    <h2>Blocks and the Buzz Meter</h2>
    <p>A <b>Block</b> (shield) cancels one card aimed at you. Everyone starts with the number the host chooses, and you can win more. The <b>Buzz Meter</b> on the leaderboard tracks who has taken the most hits so far — it's bragging rights, nothing more. Family games call it the Silly Meter.</p>

    <h2>One phone or online</h2>
    <p><b>Play here</b>: one device, 2–12 players, pass it around — it works with no signal once installed. <b>Play online</b>: everyone joins from their own phone with a five-letter code, or open a <a href="{{ route('rooms.index') }}">public room</a> so others can find it.</p>

    <h2>Play responsibly</h2>
    <p>The adults-only mode is optional and 18+. Any drink can always be swapped for water, nobody has to take a penalty they don't want to, and nobody should drive after drinking. Family mode has no alcohol at all.</p>

    <a class="cta" href="{{ route('setup', ['type' => 'party']) }}">Start a game</a>
    <x-share title="How to play Snakes & Liquor" text="Here's how to play Snakes & Liquor — a free party board game for your phone." :url="route('how-to-play')" />
</div>
@endsection
