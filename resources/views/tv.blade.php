@extends('layouts.site')

@section('robots', 'noindex,nofollow')
@section('title', 'Snakes & Liquor on TV')

@push('head')
<style>
    body { overflow-x: hidden; }
    .tvp { max-width: 70rem; margin: 0 auto; padding: 2rem 2rem 4rem; }
    .tvp h1 { font-family: 'Bungee', 'Figtree', sans-serif; font-weight: 400; font-size: clamp(2rem, 5vw, 3.6rem); margin: 1rem 0 .4rem; }
    .tvp .lead { color: #cbd5e1; font-size: clamp(1rem, 2vw, 1.5rem); font-weight: 600; margin-bottom: 1.6rem; max-width: 46rem; }
    .tv-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem; }
    .tv-step { border-radius: 1.2rem; padding: 1rem 1.2rem; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.14); font-size: clamp(.95rem, 1.6vw, 1.25rem); font-weight: 700; }
    .tv-step b { display: block; font-family: 'Bungee', 'Figtree', sans-serif; font-weight: 400; font-size: 1.6em; color: #f59e0b; }
    .tv-code { display: flex; gap: 1rem; flex-wrap: wrap; align-items: stretch; margin-bottom: 2.2rem; }
    .tv-code input { flex: 1 1 18rem; min-width: 0; text-align: center; text-transform: uppercase; letter-spacing: .35em; font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 900; padding: 1rem; border-radius: 1.2rem; background: rgba(7,6,15,.7); color: #fff; border: 3px solid rgba(255,255,255,.25); }
    .tv-code button, .tv-room { cursor: pointer; border-radius: 1.2rem; font-weight: 900; font-size: clamp(1.2rem, 2.4vw, 1.8rem); color: #fff; background: linear-gradient(135deg, #f59e0b, #ec4899); border: 3px solid transparent; padding: 1rem 2rem; text-decoration: none; }
    .tv-rooms { display: grid; grid-template-columns: repeat(auto-fill, minmax(20rem, 1fr)); gap: 1rem; }
    .tv-room { display: block; background: rgba(255,255,255,.08); border: 3px solid rgba(255,255,255,.18); }
    .tv-room small { display: block; font-weight: 700; opacity: .75; font-size: .65em; margin-top: .25rem; }
    /* remote control: a thick, obvious highlight on whatever is focused */
    .tvp a:focus, .tvp button:focus, .tvp input:focus { outline: 5px solid #34d399; outline-offset: 4px; }
    @media (max-width: 760px) { .tv-steps { grid-template-columns: 1fr; } }
</style>
@endpush

@section('body')
<div class="tvp">
    <header><x-brand /></header>
    <h1>Play on the big screen</h1>
    <p class="lead">The TV shows the board, the cards and the leaderboard. Everyone plays from their own phone — no remote needed to play.</p>

    <div class="tv-steps">
        <div class="tv-step"><b>1</b> On a phone, tap <i>Play online</i> and start a room.</div>
        <div class="tv-step"><b>2</b> Type the room code below (or pick a public game).</div>
        <div class="tv-step"><b>3</b> Friends scan the QR code on the TV to join.</div>
    </div>

    @if($errors->any())
        <div class="snl-alert err" style="font-size:1.2rem;margin-bottom:1rem;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <form class="tv-code" method="GET" action="{{ route('tv.go') }}">
        <input name="code" maxlength="8" autocomplete="off" autofocus placeholder="ROOM CODE" aria-label="Room code">
        <button type="submit">Show on TV</button>
    </form>

    @if(count($rooms))
        <h2 style="font-family:'Bungee','Figtree',sans-serif;font-weight:400;font-size:1.6rem;margin:0 0 .8rem;">Public games right now</h2>
        <div class="tv-rooms">
            @foreach($rooms as $r)
                <a class="tv-room" href="{{ route('tv.show', $r['tv']) }}">{{ $r['name'] }}
                    <small>{{ $r['open'] ? 'Waiting for players' : 'Round ' . $r['round'] }} · {{ $r['players'] }}/{{ $r['max'] }} players · {{ $r['mode']['label'] }}</small></a>
            @endforeach
        </div>
    @endif
</div>
@endsection
