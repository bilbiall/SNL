@extends('layouts.site')

@section('title', 'Snakes & Liquor — free party board game for your phone (works offline)')
@section('meta_description', 'Snakes & Liquor is a free party board game: race along a snakes-and-ladders board while every square asks for a true-or-false fact, a bluff, a ten-second challenge or a group game. Family mode for everyone, play on one phone even offline, or online with friends.')

@php
    $faq = [
        ['What is Snakes & Liquor?', 'A free party board game. You race along a snakes-and-ladders board and every square asks something of the table: a true-or-false fact, a bluff to call, a ten-second quickfire challenge, a group game, or a kind word for a friend.'],
        ['How many people can play?', 'Two to twelve. Pass one phone around the table, or play online where everyone joins from their own phone with a five-letter code.'],
        ['Is it free, and do I need an account?', 'It is free and there are no accounts. Open it, pick a mode, add the players and go.'],
        ['Does it work offline?', 'Yes. Install the app (Android, or add it to your home screen) and party mode runs on your phone with no signal. Your game is saved after every move, so you can pick up where you left off.'],
        ['Is there a family-friendly version?', 'Family mode has no alcohol at all — silly actions, facts and challenges everyone can play. The adults-only mode is optional, 18+, and any drink can always be swapped for water.'],
        ['How do public games work?', 'Create an online game, give it a name and make it public: it appears on the Public games page so others can join while it is waiting. Keep it private and only people with your code can get in.'],
        ['What kinds of squares are there?', 'True or false, fact or bluff, spotlight challenges, pick-a-friend, duels, swaps, group games, new table rules, power-ups, mystery spins, love-ladder compliments, and calm free squares.'],
    ];
@endphp

@push('jsonld')
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@graph' => [
    ['@type' => 'WebSite', 'name' => 'Snakes & Liquor', 'url' => url('/'), 'inLanguage' => 'en'],
    ['@type' => 'SoftwareApplication', 'name' => 'Snakes & Liquor', 'applicationCategory' => 'GameApplication', 'operatingSystem' => 'Android, Web, Windows, iOS (home-screen app)',
        'description' => 'A free party board game for 2–12 players with trivia, bluffing, quickfire challenges and group games. Plays offline.',
        'url' => url('/'), 'image' => asset('img/og-image.png'), 'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
        'audience' => ['@type' => 'PeopleAudience', 'suggestedMinAge' => 12], 'isAccessibleForFree' => true, 'inLanguage' => 'en'],
    ['@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faq)],
]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

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
    /* Bungee: a chunky neon-sign display face for the title */
    .title { font-family: 'Bungee', 'Figtree', system-ui, sans-serif; font-weight: 400; font-size: clamp(2rem, 8.4vw, 5.2rem); letter-spacing: .01em; line-height: 1.04; gap: .05em .3em; text-shadow: none; filter: drop-shadow(0 0 18px rgba(236,72,153,.35)) drop-shadow(0 6px 22px rgba(0,0,0,.6)); }
    .title .w span, .title .amp { filter: drop-shadow(0 .05em 0 rgba(0,0,0,.5)); }
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
    /* the token is driven by script now (smooth arcs) — no stepped keyframes */
    .mini .tile { animation: none !important; }
    .tok { animation: none !important; transform-origin: 50% 100%; }
    .tok > div { animation: none !important; }
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
    .twinkles { position: absolute; inset: 0; z-index: -1; pointer-events: none; }
    .tw { position: absolute; width: var(--s); height: var(--s); fill: #fff; opacity: 0; filter: drop-shadow(0 0 5px rgba(253,224,71,.9)); animation: twinkle 4.2s ease-in-out var(--d) infinite; }
    .tw:nth-child(3n) { fill: #fde68a; } .tw:nth-child(4n) { fill: #f9a8d4; filter: drop-shadow(0 0 5px rgba(244,114,182,.9)); }
    @keyframes twinkle { 0%, 100% { opacity: 0; transform: scale(.4) rotate(0); } 45% { opacity: .95; transform: scale(1) rotate(45deg); } 60% { opacity: .5; transform: scale(.8) rotate(60deg); } }
    .go.d { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.22); box-shadow: none; }
    .badge-n { display: inline-grid; place-items: center; min-width: 1.5rem; height: 1.5rem; padding: 0 .35rem; border-radius: 999px; background: #34d399; color: #052e1d; font-size: .8rem; font-weight: 900; }
    .about { max-width: 46rem; margin: 0 auto; padding: 2.5rem 1.2rem 3.5rem; line-height: 1.6; }
    .about h2 { font-family: 'Bungee', 'Figtree', sans-serif; font-weight: 400; font-size: clamp(1.3rem, 5vw, 1.9rem); margin: 0 0 .6rem; }
    .about p { color: #cbd5e1; font-weight: 500; }
    .about .faq { margin: .5rem 0; border-radius: 1rem; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); }
    .about .faq summary { cursor: pointer; padding: .8rem 1rem; font-weight: 800; list-style: none; }
    .about .faq summary::-webkit-details-marker { display: none; }
    .about .faq div { padding: 0 1rem .9rem; color: #cbd5e1; font-size: .92rem; }
    .about a { color: #f9a8d4; font-weight: 700; }
    .go.c { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 12px 34px rgba(16,185,129,.4); margin-bottom: 1.1rem; animation: fadeUp .6s ease both; }
    .getapp { margin-top: 1.6rem; display: grid; grid-template-columns: auto 1fr; gap: .4rem .9rem; align-items: center; max-width: 30rem; width: 100%; padding: .9rem 1rem; text-align: left; border-radius: 1.2rem; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.14); backdrop-filter: blur(8px); animation: fadeUp .8s ease 1.9s both; }
    .getapp img { width: 3.2rem; height: 3.2rem; border-radius: .9rem; box-shadow: 0 6px 18px rgba(236,72,153,.4); }
    .ga-tx { display: flex; flex-direction: column; } .ga-tx b { font-size: .95rem; } .ga-tx span { font-size: .74rem; color: #cbd5e1; font-weight: 600; line-height: 1.35; }
    .ga-btns { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: .5rem; }
    .ga-b { display: inline-flex; align-items: center; gap: .45rem; padding: .55rem .95rem; border-radius: .8rem; border: 1px solid rgba(255,255,255,.2); background: rgba(255,255,255,.1); color: #fff; font: inherit; font-weight: 800; font-size: .82rem; text-decoration: none; cursor: pointer; }
    .ga-b:hover { background: rgba(255,255,255,.18); } .ga-b small { opacity: .7; font-weight: 700; }
    .ga-main { background: linear-gradient(135deg, #10b981, #059669); border-color: transparent; box-shadow: 0 8px 22px rgba(16,185,129,.4); }
    #gaInstall { background: linear-gradient(135deg, #8b5cf6, #6366f1); border-color: transparent; }
    .ga-tip { grid-column: 1 / -1; margin: 0; font-size: .72rem; color: #94a3b8; font-weight: 600; line-height: 1.4; }
    .lp-top { position: absolute; top: 0; left: 0; right: 0; z-index: 5; display: flex; align-items: center; justify-content: flex-start; padding: .9rem 1.1rem; background: linear-gradient(180deg, rgba(7,6,15,.75), transparent); animation: fadeUp .7s ease .3s both; }
    .lp-foot { position: absolute; bottom: 1rem; left: 0; right: 0; font-size: .74rem; color: #94a3b8; animation: fadeUp .8s ease 2s both; }
    .lp-foot a { color: #94a3b8; }

    @media (max-width: 560px) {
        .mini { flex-direction: column-reverse; align-items: center; margin: 1.4rem 0 1.2rem; }
        .cta { width: 100%; max-width: 300px; display: grid; grid-template-columns: 1fr 1fr; gap: .55rem; }
        .go { padding: .65rem .7rem; border-radius: .9rem; font-size: .82rem; gap: .4rem; justify-content: center; box-shadow: 0 6px 16px rgba(0,0,0,.35); }
        .go .ic { font-size: 1.05rem; }
        .go.d { grid-column: 1 / -1; padding: .5rem .7rem; font-size: .76rem; }
        .go.c { font-size: .8rem; padding: .6rem .9rem; margin-bottom: .8rem; }
        .tag { font-size: .78rem; margin-top: .6rem; }
        .getapp { padding: .7rem .8rem; } .getapp img { width: 2.6rem; height: 2.6rem; } .ga-b { padding: .42rem .7rem; font-size: .74rem; }
    }
    /* phones: declutter — dim the busy photo, one share button, a one-line app card, footer in the flow */
    @media (max-width: 560px) {
        .lp { justify-content: flex-start; padding-top: 4.6rem; padding-bottom: 1.2rem; }
        .bg i { filter: brightness(.55) saturate(.9); }
        .wash { background: radial-gradient(ellipse 80% 55% at 50% 40%, rgba(7,6,15,.55), rgba(7,6,15,.9) 80%), linear-gradient(180deg, rgba(7,6,15,.8), rgba(7,6,15,.5) 40%, rgba(7,6,15,.95)); }
        .mini { transform: scale(.88); margin: .9rem 0 .8rem; }
        .share-row { margin-top: .8rem; gap: .35rem; }
        .share-row .share-l, .share-row .sb:not(.nat):not(.cp) { display: none; }
        .share-row .sb { padding: .3rem .75rem; font-size: .7rem; }
        .getapp { grid-template-columns: auto 1fr auto; max-width: 300px; margin-top: .9rem; padding: .5rem .65rem; border-radius: 1rem; gap: .5rem; }
        .getapp img { width: 2.2rem; height: 2.2rem; border-radius: .6rem; }
        .ga-tx span, .ga-tip, .ga-btns > .ga-b:not(.ga-main):not(#gaInstall) { display: none !important; }
        .ga-tx b { font-size: .82rem; }
        .ga-btns { grid-column: auto; flex-wrap: nowrap; }
        .ga-main small { display: none; }
        .ga-b { padding: .4rem .7rem; font-size: .72rem; }
        .lp-foot { position: static; margin-top: 1rem; font-size: .66rem; line-height: 1.6; padding: 0 1rem; }
        .lp-foot .lf-x { display: none; }
    }
    .faq-link { font: inherit; background: none; border: 0; padding: 0; color: #94a3b8; cursor: pointer; text-decoration: underline; font-size: inherit; }
    @media (prefers-reduced-motion: reduce) { .lp *, .lp *::before, .lp *::after { animation: none !important; opacity: 1 !important; transform: none !important; } .bg i:nth-child(2) { display: none; } .face:first-child { opacity: 1 !important; } }
</style>
@endpush

@section('body')
<div class="lp" id="lp">
    <div class="bg" aria-hidden="true"><i></i><i></i></div>
    <div class="sweep" aria-hidden="true"></div>
    <div class="wash" aria-hidden="true"></div>
    <div class="pieces" id="lpPieces" aria-hidden="true"></div>
    <header class="lp-top"><x-brand /></header>
    <div class="twinkles" aria-hidden="true">
        <svg class="tw" viewBox="0 0 24 24" style="left:8%;top:14%;--s:12px;--d:0s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:22%;top:7%;--s:9px;--d:1.3s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:38%;top:20%;--s:8px;--d:2.1s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:63%;top:9%;--s:10px;--d:0.7s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:80%;top:16%;--s:13px;--d:1.9s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:92%;top:28%;--s:8px;--d:2.8s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:5%;top:52%;--s:9px;--d:1.1s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:95%;top:60%;--s:11px;--d:0.4s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:14%;top:82%;--s:10px;--d:2.4s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:86%;top:86%;--s:9px;--d:1.6s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:50%;top:5%;--s:7px;--d:3.1s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
        <svg class="tw" viewBox="0 0 24 24" style="left:30%;top:92%;--s:8px;--d:0.9s" aria-hidden="true"><path d="M12 0C13 8 16 11 24 12 16 13 13 16 12 24 11 16 8 13 0 12 8 11 11 8 12 0Z"/></svg>
    </div>

    <h1 class="title" aria-label="Snakes and Liquor">
        <span class="w">@foreach(str_split('SNAKES') as $i => $ch)<span style="--i:{{ $i }}">{{ $ch }}</span>@endforeach</span>
        <span class="amp">&amp;</span>
        <span class="w">@foreach(str_split('LIQUOR') as $i => $ch)<span style="--i:{{ $i + 6 }}">{{ $ch }}</span>@endforeach</span>
    </h1>
    <p class="tag"><x-icon name="dice" /> Roll <x-icon name="self" /> Land <x-icon name="cheers" /> Sip</p>

    <div class="mini" aria-hidden="true">
        <div class="track">
            @php $tiles = [['#94a3b8','start'],['#ec4899','pick'],['#22d3ee','fact'],['#ef4444','duel'],['#34d399','trophy']]; @endphp
            @foreach($tiles as $i => [$c, $icon])<div class="tile" style="--c:{{ $c }};--i:{{ $i }}"><x-icon :name="$icon" /></div>@endforeach
            <div class="tok"><div style="overflow:hidden;"><img id="tokAv" src="{{ asset('avatars/' . $pool[0] . '.svg') }}" alt="" style="width:100%;height:100%;display:block;"></div></div>
        </div>
        <div class="die">
            @foreach([[5],[1,9],[1,5,9],[1,3,7,9],[1,3,5,7,9],[1,3,4,6,7,9]] as $n => $pips)
                <div class="face" style="--n:{{ $n }}">@for($p = 1; $p <= 9; $p++)@if(in_array($p, $pips))<b style="grid-area:{{ intdiv($p - 1, 3) + 1 }}/{{ ($p - 1) % 3 + 1 }}"></b>@endif @endfor</div>
            @endforeach
        </div>
    </div>

    <a href="{{ route('play.local') }}" class="go c" id="contBtn" style="display:none;"><x-icon name="replay" /> <span id="contTxt">Continue your game</span></a>

    <div class="cta">
        <a href="{{ route('setup', ['type' => 'party']) }}" class="go a"><x-icon name="phone" /> Play here</a>
        <a href="{{ route('setup', ['type' => 'online']) }}" class="go b"><x-icon name="globe" /> Play online</a>
        <a href="{{ route('rooms.index') }}" class="go d"><x-icon name="team" /> Public games @if(($openRooms ?? 0) > 0) <span class="badge-n">{{ $openRooms }}</span>@endif</a>
    </div>

    <x-share title="Snakes & Liquor" text="Snakes & Liquor — the free party board game where every square has a story. Play on your phone, even offline." :url="url('/')" />

    <section class="getapp" id="getApp" aria-label="Get the app" style="display:none;">
        <img src="{{ asset('icons/icon-96.png') }}" alt="" width="56" height="56">
        <div class="ga-tx"><b>Get the app</b><span>Download it once and play party mode anywhere — even with no signal.</span></div>
        <div class="ga-btns">
            @if($apk)<a class="ga-b ga-main" id="gaApk" href="{{ route('app.android') }}" download><x-icon name="phone" /> Download for Android <small>v{{ $apk['versionName'] }} · {{ number_format($apk['sizeBytes'] / 1048576, 1) }} MB</small></a>
            <a class="ga-b" href="{{ route('app.page') }}">How to install</a>@endif
            @if(is_file(public_path('downloads/desktop-version.json')))<a class="ga-b" href="{{ route('app.windows') }}" download><x-icon name="play" /> Download for Windows</a>@endif
            <button type="button" class="ga-b" id="gaInstall" style="display:none;"><x-icon name="play" /> Install from browser</button>
        </div>
        <p class="ga-tip" id="gaTip"></p>
    </section>

    <div class="lp-foot">18+ for Drinks mode · Drink responsibly · <a href="{{ route('login') }}">Admin</a> · <button type="button" class="faq-link" id="faqLink">FAQ</button></div>
</div>

<section class="about" id="aboutSec" aria-label="About Snakes & Liquor" hidden>
    <h2>A party game that is never the same twice</h2>
    <p>Snakes &amp; Liquor turns the classic snakes-and-ladders race into a night of laughs: facts to judge, bluffs to call, ten-second challenges and group games, with fresh cards every game. Play <a href="{{ route('setup', ['type' => 'party']) }}">on one phone</a>, join a <a href="{{ route('rooms.index') }}">public game</a>, or read <a href="{{ route('how-to-play') }}">how to play</a> and try the <a href="{{ route('fun-facts') }}">fun facts quiz</a>.</p>
    @foreach($faq as $f)
        <details class="faq"><summary>{{ $f[0] }}</summary><div>{{ $f[1] }}</div></details>
    @endforeach
    <p style="font-size:.78rem;color:#94a3b8;">Icons by <a href="https://game-icons.net" target="_blank" rel="noopener">game-icons.net</a> (CC BY 3.0).</p>
</section>

<script>
document.getElementById('faqLink').onclick = function () { var a = document.getElementById('aboutSec'); a.hidden = !a.hidden; if (!a.hidden) a.scrollIntoView({ behavior: 'smooth' }); };
</script>
<script>
// "Continue your game" — a party game saved on this device — and the get-the-app card.
(function () {
    try {
        var g = JSON.parse(localStorage.getItem('snl_game_v1') || 'null');
        if (g && g.room && g.room.status === 'active') {
            var n = g.players.filter(function (p) { return p.status === 'active'; }).length;
            document.getElementById('contTxt').textContent = 'Continue your game (' + n + ' players · round ' + g.room.round + ')';
            document.getElementById('contBtn').style.display = 'inline-flex';
        }
    } catch (e) {}
    var card = document.getElementById('getApp'), btn = document.getElementById('gaInstall'), tip = document.getElementById('gaTip'), apk = document.getElementById('gaApk');
    var ua = navigator.userAgent || '', ios = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1), android = /Android/i.test(ua);
    var inApp = (window.SNLPwa && SNLPwa.isStandalone()) || !!window.Capacitor || !!window.SNLDesktop;
    if (inApp) return; // already the app
    card.style.display = 'grid';
    function sync() {
        var can = window.SNLPwa && SNLPwa.canInstall();
        btn.style.display = can ? 'inline-flex' : 'none';
        if (ios) tip.textContent = 'iPhone / iPad: tap Share, then "Add to Home Screen". It then works offline like an app.';
        else if (apk) tip.textContent = android ? 'Tap Download, then open the file. If Android asks, allow "install unknown apps" for your browser once.' : 'The .apk is for Android phones — download it here and send it to your phone, or open this page on your phone. On a PC you can also install from the address bar of Chrome or Edge.';
        else tip.textContent = 'On a PC: use the install icon in the address bar of Chrome or Edge. It then opens like an app, offline too.';
    }
    btn.onclick = function () { SNLPwa.install(); };
    if (window.SNLPwa) SNLPwa.onChange(sync);
    sync();
})();
</script>

<script>
// The token hops along the squares in smooth arcs, squashes on landing, changes avatar on every landing,
// celebrates on the trophy, then pops back at the start. One requestAnimationFrame loop drives everything (no stepped keyframes).
(function () {
    var tok = document.querySelector('.tok'), img = document.getElementById('tokAv');
    var tiles = [].slice.call(document.querySelectorAll('.mini .tile'));
    if (!tok || !img || tiles.length < 3) return;
    var body = tok.firstElementChild;
    var base = '{{ asset('avatars') }}', pool = @json($pool), ai = 0;
    pool.forEach(function (k) { var p = new Image(); p.src = base + '/' + k + '.svg'; });
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var HOPS = tiles.length - 1, HOP = 620, GAP = 110, LEAD = 700;
    var ease = function (t) { return t < .5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; };  // easeInOutQuad
    var pitch = 0, t0 = performance.now(), landed = -1, size = 0;

    function measure() {
        pitch = tiles[1].offsetLeft - tiles[0].offsetLeft;
        size = tiles[0].offsetWidth;
        tok.style.willChange = 'transform';
    }
    function setAvatar() {
        ai = (ai + 1) % pool.length;
        img.src = base + '/' + pool[ai] + '.svg';
        body.animate([{ filter: 'brightness(1.7)' }, { filter: 'none' }], { duration: 300, easing: 'ease-out' });
    }
    function pop(i) {
        var el = tiles[i]; if (!el) return;
        el.animate([{ transform: 'translateY(0) scale(1)', filter: 'brightness(1)' }, { transform: 'translateY(5px) scale(1.12, .9)', filter: 'brightness(1.4)', offset: .35 }, { transform: 'translateY(0) scale(1)', filter: 'brightness(1)' }], { duration: 340, easing: 'cubic-bezier(.3,.7,.4,1)' });
    }
    var CYCLE = LEAD + HOPS * (HOP + GAP) + 1500 + 700;

    function frame(now) {
        if (!pitch) measure();
        var t = (now - t0) % CYCLE, x = 0, y = 0, sx = 1, sy = 1, rot = 0, op = 1, sc = 1;
        var run = t - LEAD;
        if (run < 0) {                                   // pop in at the start
            sc = Math.min(1, Math.max(0, 1 - (-run) / LEAD * 0)) ;
            if (t < 260) { var u = t / 260; sc = 1 - Math.pow(1 - u, 3); sc = sc * (1 + Math.sin(u * Math.PI) * .18); }
            if (landed !== -1 && t < 50) landed = -1;
        } else if (run < HOPS * (HOP + GAP)) {
            var h = Math.floor(run / (HOP + GAP)), local = run - h * (HOP + GAP);
            if (local < HOP) {
                var u = local / HOP;
                x = (h + ease(u)) * pitch;
                y = -Math.sin(Math.PI * u) * size * 1.05;            // a real parabola-like arc
                var stretch = Math.sin(Math.PI * u) * .12;
                sy = 1 + stretch; sx = 1 - stretch * .6;              // stretch while airborne
                rot = (u - .5) * 14;
            } else {                                                  // landing squash, settling
                var s = (local - HOP) / GAP;
                x = (h + 1) * pitch;
                var sq = Math.sin((1 - s) * Math.PI / 2) * .2;
                sy = 1 - sq; sx = 1 + sq * .8;
                if (landed < h) { landed = h; pop(h + 1); setAvatar(); }
            }
        } else {
            var c = run - HOPS * (HOP + GAP);                          // celebrate on the trophy
            x = HOPS * pitch;
            if (c < 1500) {
                var cu = c / 750, bounce = Math.abs(Math.sin(cu * Math.PI));
                y = -bounce * size * (cu < 1 ? 1.4 : .9);
                rot = Math.sin(cu * Math.PI * 2) * 18;
                sy = 1 + bounce * .1; sx = 1 - bounce * .06;
                if (c < 40 && landed !== 99) { landed = 99; pop(HOPS); }
            } else {                                                   // shrink away, ready to pop back in
                var f = Math.min(1, (c - 1500) / 600);
                x = HOPS * pitch; sc = 1 - ease(f); op = 1 - f;
                if (landed === 99 && f >= 1) landed = -1;
            }
        }
        tok.style.transform = 'translate3d(' + x.toFixed(2) + 'px,' + y.toFixed(2) + 'px,0) rotate(' + rot.toFixed(2) + 'deg) scale(' + (sx * sc).toFixed(3) + ',' + (sy * sc).toFixed(3) + ')';
        tok.style.opacity = op;
        requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
    window.addEventListener('resize', function () { pitch = 0; });
})();
</script>

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
