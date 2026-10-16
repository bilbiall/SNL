@extends('layouts.site')

@section('title', 'Snakes & Liquor')

@push('head')
<style>
    body { overflow-x: hidden; background: #07060f; }
    .lp { position: relative; min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 2.2rem 1.2rem 4.5rem; overflow: hidden; isolation: isolate; }

    /* ── Immersive background: two real photos cross-fade and slowly drift, dimmed under a dark wash ── */
    .bg { position: absolute; inset: 0; z-index: -3; overflow: hidden; background: #07060f; }
    .bg i { position: absolute; inset: -6%; background-size: cover; background-position: center; opacity: 0; animation: bgFade 26s ease-in-out infinite, bgDrift 26s ease-in-out infinite alternate; }
    .bg i:nth-child(1) { background-image: url('{{ asset('img/landing/neon-bar.jpg') }}'); }
    .bg i:nth-child(2) { background-image: url('{{ asset('img/landing/dice-chips.jpg') }}'); animation-delay: -13s, -13s; background-position: 40% 60%; }
    @keyframes bgFade { 0%, 6% { opacity: 0; } 18%, 46% { opacity: .5; } 58%, 100% { opacity: 0; } }
    @keyframes bgDrift { from { transform: scale(1.04) translate(-1.2%, 0.8%); } to { transform: scale(1.16) translate(1.2%, -0.8%); } }
    .wash { position: absolute; inset: 0; z-index: -2; background:
        radial-gradient(ellipse 70% 60% at 50% 38%, rgba(7,6,15,.2), rgba(7,6,15,.82) 78%),
        linear-gradient(180deg, rgba(7,6,15,.7), rgba(7,6,15,.35) 40%, rgba(7,6,15,.9)); }
    /* a faint moving glow that sweeps across the whole page */
    .sweep { position: absolute; inset: -20%; z-index: -2; pointer-events: none; background: conic-gradient(from 0deg at 50% 60%, transparent 0 70%, rgba(245,158,11,.10) 80%, transparent 90%, rgba(236,72,153,.10) 96%, transparent); animation: spin 38s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Title: letters drop in with a spring, then a metallic shine sweeps across ── */
    .title { position: relative; font-size: clamp(2.6rem, 11.5vw, 6.6rem); line-height: .95; font-weight: 900; letter-spacing: -.03em; margin: 0; display: flex; flex-wrap: wrap; justify-content: center; gap: 0 .26em; text-shadow: 0 6px 30px rgba(0,0,0,.6); }
    .title .w { display: inline-flex; }
    .title .w span { display: inline-block; background: linear-gradient(180deg, #fff 30%, #cbd5e1); -webkit-background-clip: text; background-clip: text; color: transparent; animation: dropIn .75s cubic-bezier(.3,1.5,.5,1) both; animation-delay: calc(var(--i) * 60ms); }
    .title .amp { display: inline-block; background: linear-gradient(135deg, #f59e0b, #ec4899); -webkit-background-clip: text; background-clip: text; color: transparent; animation: dropIn .75s cubic-bezier(.3,1.5,.5,1) .55s both, wobble 4.2s ease-in-out 1.5s infinite; }
    .title::after { content: ''; position: absolute; inset: 0; background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.55) 50%, transparent 60%); mix-blend-mode: overlay; transform: translateX(-120%); animation: shine 6s ease-in-out 2.2s infinite; pointer-events: none; }
    @keyframes dropIn { from { opacity: 0; transform: translateY(-70px) rotate(-10deg) scale(.6); } to { opacity: 1; transform: none; } }
    @keyframes wobble { 0%,100% { transform: rotate(-6deg) scale(1); } 50% { transform: rotate(6deg) scale(1.1); } }
    @keyframes shine { 0%, 55% { transform: translateX(-120%); } 85%, 100% { transform: translateX(120%); } }
    .tag { display: flex; align-items: center; justify-content: center; gap: .7rem; font-size: clamp(1rem, 3.4vw, 1.5rem); font-weight: 800; color: #e2e8f0; margin: .9rem 0 0; letter-spacing: .08em; text-transform: uppercase; animation: fadeUp .8s ease 1s both; }
    .tag .ic { color: #f59e0b; font-size: 1.15em; animation: tagPulse 2.2s ease-in-out infinite; }
    .tag .ic:nth-of-type(2) { color: #ec4899; animation-delay: .35s; } .tag .ic:nth-of-type(3) { color: #8b5cf6; animation-delay: .7s; }
    @keyframes tagPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.25) rotate(8deg); } }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }

    /* ── Mini board: a token hops along, snakes it back once, then climbs to the trophy ── */
    .mini { --t: clamp(30px, 8.4vw, 54px); --pitch: calc(var(--t) * 1.2); margin: 2.2rem 0 2rem; display: flex; align-items: flex-end; gap: calc(var(--t) * .7); animation: fadeUp .8s ease 1.2s both; }
    .track { position: relative; display: flex; gap: calc(var(--t) * .2); padding-top: calc(var(--t) * 1.1); }
    .tile { width: var(--t); height: var(--t); border-radius: 24%; display: flex; align-items: center; justify-content: center; font-size: calc(var(--t) * .56); color: rgba(10,8,20,.85); background: linear-gradient(160deg, color-mix(in srgb, var(--c) 100%, #fff 18%), var(--c)); box-shadow: inset 0 -4px 0 rgba(0,0,0,.25), 0 8px 16px rgba(0,0,0,.5); animation: tilePop 10s ease-in-out infinite; animation-delay: calc(var(--i) * 1s - .35s); }
    .tile:first-child { animation: none; }
    @keyframes tilePop { 0%, 9% { transform: none; filter: none; } 3.5% { transform: translateY(6px) scale(1.14); filter: brightness(1.4); } }
    .tok { position: absolute; left: 0; bottom: calc(var(--t) * .98); width: var(--t); height: var(--t); animation: hopX 10s linear infinite; }
    .tok > div { width: 100%; height: 100%; border-radius: 50%; border: 3px solid #fff; display: flex; align-items: center; justify-content: center; font-size: calc(var(--t) * .56); color: #fff; background: radial-gradient(circle at 30% 25%, rgba(255,255,255,.55), transparent 45%), #f59e0b; box-shadow: 0 8px 14px rgba(0,0,0,.55); animation: hopY 10s linear infinite; }
    @keyframes hopX {
        0%, 3% { transform: translateX(0); } 10% { transform: translateX(calc(var(--pitch) * 1)); } 20% { transform: translateX(calc(var(--pitch) * 2)); }
        30% { transform: translateX(calc(var(--pitch) * 3)); } 40% { transform: translateX(calc(var(--pitch) * 4)); } 50% { transform: translateX(calc(var(--pitch) * 5)); }
        60% { transform: translateX(calc(var(--pitch) * 6)); } 70% { transform: translateX(calc(var(--pitch) * 7)); } 80%, 94% { transform: translateX(calc(var(--pitch) * 8)); }
        100% { transform: translateX(0); }
    }
    @keyframes hopY {
        0%, 3% { transform: translateY(0); } 6.5% { transform: translateY(-120%); } 10% { transform: translateY(0); } 16.5% { transform: translateY(-120%); } 20% { transform: translateY(0); }
        26.5% { transform: translateY(-120%); } 30% { transform: translateY(0); } 36.5% { transform: translateY(-120%); } 40% { transform: translateY(0); } 46.5% { transform: translateY(-120%); } 50% { transform: translateY(0); }
        56.5% { transform: translateY(-120%); } 60% { transform: translateY(0); } 66.5% { transform: translateY(-120%); } 70% { transform: translateY(0); } 76.5% { transform: translateY(-120%); } 80% { transform: translateY(0); }
        88% { transform: translateY(-45%) scale(1.18) rotate(14deg); } 94% { transform: translateY(0) scale(1) rotate(0); } 100% { transform: translateY(0); }
    }
    .die { position: relative; width: calc(var(--t) * 1.2); height: calc(var(--t) * 1.2); animation: tumble 2.4s ease-in-out infinite; flex-shrink: 0; }
    @keyframes tumble { 0%, 100% { transform: translateY(0) rotate(0); } 20% { transform: translateY(-18px) rotate(90deg); } 40% { transform: translateY(0) rotate(180deg); } 55% { transform: translateY(-8px) rotate(270deg); } 70% { transform: translateY(0) rotate(360deg); } }
    .face { position: absolute; inset: 0; border-radius: 24%; background: linear-gradient(155deg, #fff, #dfe3ea); box-shadow: inset 0 -4px 0 rgba(0,0,0,.14), 0 8px 16px rgba(0,0,0,.5); display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr); padding: 17%; opacity: 0; animation: face 6s steps(1) infinite; animation-delay: calc(var(--n) * 1s); }
    .face b { width: 72%; height: 72%; border-radius: 50%; background: #1b1830; align-self: center; justify-self: center; }
    @keyframes face { 0% { opacity: 1; } 16.6%, 100% { opacity: 0; } }

    /* ── Actions ── */
    .cta { display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; animation: fadeUp .8s ease 1.5s both; }
    .go { position: relative; overflow: hidden; display: inline-flex; align-items: center; gap: .7rem; padding: 1.05rem 2rem; border-radius: 1.2rem; font-size: 1.15rem; font-weight: 900; color: #fff; text-decoration: none; transition: transform .15s; }
    .go::after { content: ''; position: absolute; inset: 0; background: linear-gradient(105deg, transparent 38%, rgba(255,255,255,.4) 50%, transparent 62%); transform: translateX(-130%); animation: btnShine 4.5s ease-in-out infinite; }
    .go.b::after { animation-delay: 1.2s; }
    @keyframes btnShine { 0%, 60% { transform: translateX(-130%); } 90%, 100% { transform: translateX(130%); } }
    .go:hover { transform: translateY(-3px) scale(1.04); } .go:active { transform: scale(.97); }
    .go .ic { font-size: 1.6rem; }
    .go.a { background: linear-gradient(135deg, #f59e0b, #ec4899); box-shadow: 0 12px 34px rgba(236,72,153,.45); animation: breathe 2.6s ease-in-out 2.2s infinite; }
    .go.b { background: linear-gradient(135deg, #8b5cf6, #6366f1); box-shadow: 0 12px 34px rgba(99,102,241,.45); }
    @keyframes breathe { 0%,100% { box-shadow: 0 12px 34px rgba(236,72,153,.45); } 50% { box-shadow: 0 12px 50px rgba(236,72,153,.8); } }
    .code { display: flex; gap: .5rem; margin-top: 1.5rem; animation: fadeUp .8s ease 1.7s both; }
    .code input { width: 10.5rem; text-align: center; text-transform: uppercase; letter-spacing: .25em; font-weight: 900; background: rgba(7,6,15,.6); backdrop-filter: blur(6px); }
    .lp-foot { position: absolute; bottom: 1rem; left: 0; right: 0; font-size: .74rem; color: #94a3b8; animation: fadeUp .8s ease 2s both; }
    .lp-foot a { color: #94a3b8; }

    @media (max-width: 560px) { .go { width: 100%; justify-content: center; } .cta { width: 100%; max-width: 360px; } .mini { flex-direction: column-reverse; align-items: center; } }
    @media (prefers-reduced-motion: reduce) { .lp *, .lp *::before, .lp *::after { animation: none !important; opacity: 1 !important; transform: none !important; } .bg i:nth-child(2) { display: none; } .face:first-child { opacity: 1 !important; } }
</style>
@endpush

@section('body')
<div class="lp" id="lp">
    <div class="bg" aria-hidden="true"><i></i><i></i></div>
    <div class="sweep" aria-hidden="true"></div>
    <div class="wash" aria-hidden="true"></div>
    <div class="pieces" id="lpPieces" aria-hidden="true"></div>

    <h1 class="title" aria-label="Snakes and Liquor">
        <span class="w">@foreach(str_split('SNAKES') as $i => $ch)<span style="--i:{{ $i }}">{{ $ch }}</span>@endforeach</span>
        <span class="amp">&amp;</span>
        <span class="w">@foreach(str_split('LIQUOR') as $i => $ch)<span style="--i:{{ $i + 6 }}">{{ $ch }}</span>@endforeach</span>
    </h1>
    <p class="tag"><x-icon name="dice" /> Roll <x-icon name="self" /> Land <x-icon name="duel" /> Dare</p>

    <div class="mini" aria-hidden="true">
        <div class="track">
            @php $tiles = [['#94a3b8','start'],['#f59e0b','self'],['#ec4899','pick'],['#10b981','everyone'],['#ef4444','duel'],['#22d3ee','fact'],['#a78bfa','swap'],['#facc15','power'],['#fb7185','trophy']]; @endphp
            @foreach($tiles as $i => [$c, $icon])<div class="tile" style="--c:{{ $c }};--i:{{ $i }}"><x-icon :name="$icon" /></div>@endforeach
            <div class="tok"><div><x-icon name="av-tiger" /></div></div>
        </div>
        <div class="die">
            @foreach([[5],[1,9],[1,5,9],[1,3,7,9],[1,3,5,7,9],[1,3,4,6,7,9]] as $n => $pips)
                <div class="face" style="--n:{{ $n }}">@for($p = 1; $p <= 9; $p++)@if(in_array($p, $pips))<b style="grid-area:{{ intdiv($p - 1, 3) + 1 }}/{{ ($p - 1) % 3 + 1 }}"></b>@endif @endfor</div>
            @endforeach
        </div>
    </div>

    <div class="cta">
        <a href="{{ route('setup', ['type' => 'party']) }}" class="go a"><x-icon name="phone" /> Play here</a>
        <a href="{{ route('setup', ['type' => 'online']) }}" class="go b"><x-icon name="globe" /> Play online</a>
    </div>

    <form action="{{ route('join') }}" method="GET" class="code">
        <input name="code" class="snl-input" placeholder="CODE" maxlength="8" autocomplete="off" aria-label="Room code">
        <button class="btn btn-ghost">Join</button>
    </form>

    <div class="lp-foot">18+ for Drinks mode · Drink responsibly · Icons <a href="https://game-icons.net" target="_blank" rel="noopener">game-icons.net</a> · <a href="{{ route('login') }}">Admin</a></div>
</div>

<script>
// Game pieces pop in around the edges, then drift; they lean gently away from the pointer.
(function () {
    var box = document.getElementById('lpPieces'), lp = document.getElementById('lp');
    var base = '{{ asset('icons.svg') }}';
    var set = ['dice', 'cards', 'cheers', 'martini', 'cork', 'coin', 'snake', 'ladder', 'ace', 'crown', 'wine', 'stein', 'shield', 'power', 'trophy', 'joker', 'bottle', 'duel'];
    var colors = ['#f59e0b', '#ec4899', '#8b5cf6', '#22d3ee', '#34d399', '#f87171', '#facc15'];
    var r = function (a, b) { return a + Math.random() * (b - a); };
    var els = [];
    set.forEach(function (name, i) {
        var left = i % 2 === 0, el = document.createElement('div');
        el.className = 'pc';
        el.style.cssText = '--x:' + (left ? r(2, 22) : r(76, 94)).toFixed(1) + '%;--y:' + r(4, 90).toFixed(1) + '%;--s:' + r(26, 62).toFixed(0) + 'px;--r:' + r(-28, 28).toFixed(0) + 'deg;--o:' + r(.35, .7).toFixed(2) +
            ';--c:' + colors[i % colors.length] + ';--dl:' + (0.35 + i * 0.16 + r(0, .3)).toFixed(2) + 's;--bd:' + r(6, 12).toFixed(1) + 's;--dx:' + r(8, 20).toFixed(0) + 'px;--dy:' + r(12, 26).toFixed(0) + 'px';
        el.innerHTML = '<svg class="ic" aria-hidden="true"><use href="' + base + '#i-' + name + '"/></svg>';
        el.dataset.depth = r(.4, 1.6).toFixed(2);
        box.appendChild(el); els.push(el);
    });
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    lp.addEventListener('pointermove', function (e) {
        var x = (e.clientX / innerWidth - .5), y = (e.clientY / innerHeight - .5);
        els.forEach(function (el) { el.firstChild.style.transform = 'translate(' + (-x * 26 * el.dataset.depth).toFixed(1) + 'px,' + (-y * 20 * el.dataset.depth).toFixed(1) + 'px)'; });
    });
})();
</script>
@endsection
