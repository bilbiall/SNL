@extends('layouts.site')

@section('title', 'About Snakes & Liquor — a free party board game from Kenya')
@section('meta_description', 'Snakes & Liquor is a free party board game from Kenya: a snakes-and-ladders race where every square asks something of the table. Play on one phone or online with friends.')

@push('head')
<style>
    body { background: radial-gradient(ellipse at 50% 0%, #2a1550, #0b0a16 62%); min-height: 100vh; }
    .doc { max-width: 46rem; margin: 0 auto; padding: 1rem 1.1rem 3.5rem; line-height: 1.6; }
    .doc h1 { font-family: 'Bungee', 'Figtree', sans-serif; font-weight: 400; font-size: clamp(1.6rem, 6vw, 2.4rem); line-height: 1.1; margin: 1.2rem 0 .5rem; }
    .doc h2 { font-size: 1.15rem; font-weight: 900; margin: 2rem 0 .5rem; color: #fbbf24; }
    .doc p, .doc li { color: #dbe3ee; font-weight: 500; } .doc a { color: #f9a8d4; }
    .doc ul { padding-left: 1.2rem; list-style: disc; } .doc li { margin: .3rem 0; }
    .doc .cta { display: inline-flex; margin-top: 1.2rem; padding: .8rem 1.3rem; border-radius: 1rem; font-weight: 900; color: #fff; text-decoration: none; background: linear-gradient(135deg, #f59e0b, #ec4899); }
</style>
@endpush

@section('body')
<div class="doc">
    <x-brand />
    <h1>About Snakes &amp; Liquor</h1>
    <p>Snakes &amp; Liquor turns the classic snakes-and-ladders race into a night of laughs. Roll the die, hop along the board and see what each square asks of the table: a true-or-false fact to judge, a bluff to call, a ten-second challenge, a group game or a kind word for someone you are playing with. The cards are fresh every game, so no two evenings are the same.</p>

    <h2>Ways to play</h2>
    <ul>
        <li><b>Party mode</b> — pass one phone around the table. It works with no signal once loaded.</li>
        <li><b>Online</b> — host a room, share the code or QR, and everyone joins from their own phone. Put it on a TV from <a href="{{ route('tv.index') }}">/tv</a> for a big shared board.</li>
        <li><b>Family, Boyz or Girlies</b> — Family mode suits every age. Boyz and Girlies have their own banter, dares and trivia for a group of guy friends or girl friends, with no alcohol.</li>
        <li><b>Drinks</b> — the adults-only mode, for people 18 and over.</li>
    </ul>
    <p>New here? Read <a href="{{ route('how-to-play') }}">how to play</a> or try the <a href="{{ route('fun-facts') }}">fun facts quiz</a>. The game is also available as an <a href="{{ route('app.page') }}">Android app</a>.</p>

    <h2>Free, with a few ads</h2>
    <p>The game is free to play. To keep it that way, the website may show a short ad when a host starts a new game or taps Play again. Ads never appear during play, and the apps do not show web ads. Details are in our <a href="{{ route('privacy') }}">privacy policy</a>.</p>

    <h2>Play responsibly</h2>
    <p>Snakes &amp; Liquor is a game, not a drinking contest. Nobody has to drink, and Family mode has no drinking at all. If you do drink, keep it to people of legal drinking age, pace yourselves, look after each other and never drive afterwards.</p>

    <h2>Get in touch</h2>
    @if($email)
        <p>Feedback, bug reports, removal requests or partnership ideas: <a href="mailto:{{ $email }}">{{ $email }}</a>.</p>
    @else
        <p>Feedback and bug reports are always welcome.</p>
    @endif
    <p>Icons by <a href="https://game-icons.net" target="_blank" rel="noopener">game-icons.net</a> (CC BY 3.0).</p>

    <a class="cta" href="{{ route('setup', ['type' => 'party']) }}">Play now</a>
</div>
@endsection
