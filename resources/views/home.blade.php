@extends('layouts.site')

@section('title', 'Snakes & Liquor')

@push('head')
<style>
    body { overflow-x: hidden; }
    .lp { position: relative; min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 2rem 1.2rem 4.5rem; overflow: hidden; isolation: isolate; }

    /* ── background: champagne bubbles rising + slow-drifting game pieces ── */
    .fizz { position: absolute; inset: 0; z-index: -1; pointer-events: none; }
    .fizz i {
        position: absolute; bottom: -40px; left: var(--l); width: var(--s); height: var(--s); border-radius: 50%;
        border: 1.5px solid rgba(255,255,255,.28); background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.35), rgba(255,255,255,.04) 60%);
        animation: rise var(--d) linear var(--w) infinite; opacity: 0;
    }
    @keyframes rise { 0% { transform: translate(0,0) scale(.6); opacity: 0; } 10% { opacity: .9; } 50% { transform: translate(14px,-55vh) scale(1); } 100% { transform: translate(-10px,-115vh) scale(1.15); opacity: 0; } }
    .drift { position: absolute; z-index: -1; font-size: clamp(1.6rem, 5vw, 2.8rem); opacity: .22; animation: drift 9s ease-in-out infinite; pointer-events: none; }
    @keyframes drift { 0%,100% { transform: translate(0,0) rotate(-6deg); } 50% { transform: translate(14px,-22px) rotate(8deg); } }

    /* ── title: letters drop in, then the ampersand keeps a slow wobble ── */
    .title { font-size: clamp(2.6rem, 11vw, 6.2rem); line-height: .95; font-weight: 900; letter-spacing: -.03em; margin: 0; display: flex; flex-wrap: wrap; justify-content: center; gap: 0 .28em; }
    .title .w { display: inline-flex; }
    .title .w span { display: inline-block; animation: dropIn .7s cubic-bezier(.3,1.5,.5,1) both; animation-delay: calc(var(--i) * 55ms); }
    .title .amp { background: linear-gradient(135deg, #f59e0b, #ec4899); -webkit-background-clip: text; background-clip: text; color: transparent; animation: dropIn .7s cubic-bezier(.3,1.5,.5,1) .5s both, wobble 4s ease-in-out 1.4s infinite; display: inline-block; }
    @keyframes dropIn { from { opacity: 0; transform: translateY(-60px) rotate(-8deg) scale(.7); } to { opacity: 1; transform: none; } }
    @keyframes wobble { 0%,100% { transform: rotate(-6deg) scale(1); } 50% { transform: rotate(6deg) scale(1.08); } }
    .tag { font-size: clamp(1.05rem, 3.6vw, 1.6rem); font-weight: 800; color: #cbd5e1; margin: .7rem 0 0; letter-spacing: .04em; animation: fadeUp .8s ease 1s both; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

    /* ── the animated mini board: a token hops along, a die tumbles ── */
    .stage-mini { --t: clamp(30px, 8.6vw, 52px); --pitch: calc(var(--t) * 1.2); margin: 2.2rem 0 2rem; display: flex; align-items: flex-end; gap: calc(var(--t) * .6); animation: fadeUp .8s ease 1.2s both; }
    .track { position: relative; display: flex; gap: calc(var(--t) * .2); padding-top: calc(var(--t) * .9); }
    .tile {
        width: var(--t); height: var(--t); border-radius: 22%; display: flex; align-items: center; justify-content: center; font-size: calc(var(--t) * .5);
        background: var(--c); box-shadow: inset 0 -4px 0 rgba(0,0,0,.22), 0 6px 14px rgba(0,0,0,.4);
        animation: tilePop 10s ease-in-out infinite; animation-delay: calc(var(--i) * 1s - 0.35s);
    }
    .tile:first-child { animation: none; }
    @keyframes tilePop { 0%, 9% { transform: none; filter: none; } 3.5% { transform: translateY(5px) scale(1.12); filter: brightness(1.35); } }
    .token-mini { position: absolute; left: 0; bottom: calc(var(--t) * 0.98); width: var(--t); height: var(--t); animation: hopX 10s linear infinite; }
    .token-mini > div {
        width: 100%; height: 100%; border-radius: 50%; border: 3px solid #fff; display: flex; align-items: center; justify-content: center; font-size: calc(var(--t) * .55);
        background: radial-gradient(circle at 30% 25%, rgba(255,255,255,.55), transparent 45%), #f59e0b; box-shadow: 0 6px 12px rgba(0,0,0,.5); animation: hopY 10s linear infinite;
    }
    @keyframes hopX {
        0%, 3% { transform: translateX(0); }
        10% { transform: translateX(calc(var(--pitch) * 1)); } 20% { transform: translateX(calc(var(--pitch) * 2)); }
        30% { transform: translateX(calc(var(--pitch) * 3)); } 40% { transform: translateX(calc(var(--pitch) * 4)); }
        50% { transform: translateX(calc(var(--pitch) * 5)); } 60% { transform: translateX(calc(var(--pitch) * 6)); }
        70% { transform: translateX(calc(var(--pitch) * 7)); } 80%, 94% { transform: translateX(calc(var(--pitch) * 8)); }
        100% { transform: translateX(0); }
    }
    @keyframes hopY {
        0%, 3% { transform: translateY(0); }
        6.5% { transform: translateY(-120%); } 10% { transform: translateY(0); } 16.5% { transform: translateY(-120%); } 20% { transform: translateY(0); }
        26.5% { transform: translateY(-120%); } 30% { transform: translateY(0); } 36.5% { transform: translateY(-120%); } 40% { transform: translateY(0); }
        46.5% { transform: translateY(-120%); } 50% { transform: translateY(0); } 56.5% { transform: translateY(-120%); } 60% { transform: translateY(0); }
        66.5% { transform: translateY(-120%); } 70% { transform: translateY(0); } 76.5% { transform: translateY(-120%); } 80% { transform: translateY(0); }
        88% { transform: translateY(-40%) scale(1.15) rotate(12deg); } 94% { transform: translateY(0) scale(1) rotate(0); } 100% { transform: translateY(0); }
    }
    .die { position: relative; width: calc(var(--t) * 1.15); height: calc(var(--t) * 1.15); animation: tumble 2.4s ease-in-out infinite; flex-shrink: 0; }
    @keyframes tumble { 0%, 100% { transform: translateY(0) rotate(0); } 20% { transform: translateY(-16px) rotate(90deg); } 40% { transform: translateY(0) rotate(180deg); } 55% { transform: translateY(-7px) rotate(270deg); } 70% { transform: translateY(0) rotate(360deg); } }
    .face { position: absolute; inset: 0; border-radius: 22%; background: linear-gradient(155deg, #fff, #dfe3ea); box-shadow: inset 0 -4px 0 rgba(0,0,0,.14), 0 6px 14px rgba(0,0,0,.4); display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr); padding: 17%; opacity: 0; animation: face 6s steps(1) infinite; animation-delay: calc(var(--n) * 1s); }
    .face b { width: 72%; height: 72%; border-radius: 50%; background: #1b1830; align-self: center; justify-self: center; }
    @keyframes face { 0% { opacity: 1; } 16.6%, 100% { opacity: 0; } }

    /* ── actions ── */
    .cta { display: flex; gap: .9rem; flex-wrap: wrap; justify-content: center; animation: fadeUp .8s ease 1.5s both; }
    .go { display: inline-flex; align-items: center; gap: .6rem; padding: 1.05rem 1.9rem; border-radius: 1.2rem; font-size: 1.15rem; font-weight: 900; color: #fff; text-decoration: none; position: relative; transition: transform .15s; }
    .go:hover { transform: translateY(-3px) scale(1.03); } .go:active { transform: scale(.97); }
    .go.a { background: linear-gradient(135deg, #f59e0b, #ec4899); box-shadow: 0 10px 30px rgba(236,72,153,.4); animation: breathe 2.6s ease-in-out 2.2s infinite; }
    .go.b { background: linear-gradient(135deg, #8b5cf6, #6366f1); box-shadow: 0 10px 30px rgba(99,102,241,.4); }
    @keyframes breathe { 0%,100% { box-shadow: 0 10px 30px rgba(236,72,153,.4); } 50% { box-shadow: 0 10px 44px rgba(236,72,153,.75); } }
    .go .ic { font-size: 1.5rem; }
    .code { display: flex; gap: .5rem; margin-top: 1.4rem; animation: fadeUp .8s ease 1.7s both; }
    .code input { width: 10.5rem; text-align: center; text-transform: uppercase; letter-spacing: .25em; font-weight: 900; }
    .lp-foot { position: absolute; bottom: 1rem; left: 0; right: 0; font-size: .74rem; color: #6b7280; animation: fadeUp .8s ease 2s both; }
    .lp-foot a { color: #6b7280; }

    @media (max-width: 520px) { .go { width: 100%; justify-content: center; } .cta { width: 100%; max-width: 360px; } .stage-mini { flex-direction: column-reverse; align-items: center; } }
    @media (prefers-reduced-motion: reduce) { .lp *, .lp *::before { animation: none !important; opacity: 1 !important; } .fizz { display: none; } .face:first-child { opacity: 1 !important; } }
</style>
@endpush

@section('body')
<div class="lp">
    <div class="fizz" aria-hidden="true">
        @for($i = 0; $i < 24; $i++)
            <i style="--l:{{ rand(2, 98) }}%; --s:{{ rand(8, 28) }}px; --d:{{ rand(8, 17) }}s; --w:-{{ rand(0, 16) }}s"></i>
        @endfor
    </div>
    <span class="drift" style="left:7%;top:18%;animation-delay:-1s" aria-hidden="true">🐍</span>
    <span class="drift" style="right:8%;top:24%;animation-delay:-4s" aria-hidden="true">🪜</span>
    <span class="drift" style="left:12%;bottom:22%;animation-delay:-6s" aria-hidden="true">🥂</span>
    <span class="drift" style="right:11%;bottom:20%;animation-delay:-3s" aria-hidden="true">🎲</span>

    <h1 class="title" aria-label="Snakes and Liquor">
        <span class="w">@foreach(str_split('SNAKES') as $i => $ch)<span style="--i:{{ $i }}">{{ $ch }}</span>@endforeach</span>
        <span class="amp">&amp;</span>
        <span class="w">@foreach(str_split('LIQUOR') as $i => $ch)<span style="--i:{{ $i + 6 }}">{{ $ch }}</span>@endforeach</span>
    </h1>
    <p class="tag">Roll. Land. Dare.</p>

    <div class="stage-mini" aria-hidden="true">
        <div class="track">
            @php $tiles = [['#64748b','🏁'],['#f59e0b','🎯'],['#ec4899','👉'],['#10b981','🙌'],['#ef4444','⚔️'],['#06b6d4','💡'],['#8b5cf6','🔀'],['#eab308','⚡'],['#f43f5e','🏆']]; @endphp
            @foreach($tiles as $i => [$c, $e])<div class="tile" style="--c:{{ $c }};--i:{{ $i }}">{{ $e }}</div>@endforeach
            <div class="token-mini"><div>🦊</div></div>
        </div>
        <div class="die">
            @foreach([[5],[1,9],[1,5,9],[1,3,7,9],[1,3,5,7,9],[1,3,4,6,7,9]] as $n => $pips)
                <div class="face" style="--n:{{ $n }}">@for($p = 1; $p <= 9; $p++)@if(in_array($p, $pips))<b style="grid-area:{{ intdiv($p - 1, 3) + 1 }}/{{ ($p - 1) % 3 + 1 }}"></b>@endif @endfor</div>
            @endforeach
        </div>
    </div>

    <div class="cta">
        <a href="{{ route('setup', ['type' => 'party']) }}" class="go a"><span class="ic">📱</span> Play here</a>
        <a href="{{ route('setup', ['type' => 'online']) }}" class="go b"><span class="ic">🌍</span> Play online</a>
    </div>

    <form action="{{ route('join') }}" method="GET" class="code">
        <input name="code" class="snl-input" placeholder="CODE" maxlength="8" autocomplete="off" aria-label="Room code">
        <button class="btn btn-ghost">Join</button>
    </form>

    <div class="lp-foot">18+ for Drinks mode · Drink responsibly · <a href="{{ route('login') }}">Admin</a></div>
</div>
@endsection
