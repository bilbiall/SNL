@extends('layouts.site')

@section('title', 'Public games — join an online party room | Snakes & Liquor')
@section('meta_description', 'Browse live Snakes & Liquor rooms and jump into a party game with friends or new people — or open your own public room. Free, no account needed.')

@push('head')
<style>
    body { background: radial-gradient(ellipse at 50% 0%, #2a1550, #0b0a16 62%); min-height: 100vh; }
    .rm { max-width: 46rem; margin: 0 auto; padding: 1rem 1.1rem 3rem; }
    .rm-top { display: flex; align-items: center; justify-content: space-between; gap: .6rem; flex-wrap: wrap; }
    .rm h1 { font-family: 'Bungee', 'Figtree', sans-serif; font-weight: 400; font-size: clamp(1.5rem, 6vw, 2.2rem); margin: 1.1rem 0 .2rem; }
    .rm .sub { color: #cbd5e1; font-weight: 600; margin: 0 0 1rem; }
    .rm-cta { display: flex; flex-wrap: wrap; gap: .6rem; margin-bottom: 1.1rem; }
    .rm-b { display: inline-flex; align-items: center; gap: .5rem; padding: .75rem 1.1rem; border-radius: 1rem; font-weight: 900; color: #fff; text-decoration: none; border: 1px solid rgba(255,255,255,.18); background: rgba(255,255,255,.08); font-size: .9rem; }
    .rm-b.go { background: linear-gradient(135deg, #f59e0b, #ec4899); border-color: transparent; box-shadow: 0 10px 28px rgba(236,72,153,.4); }
    .rm-live { display: inline-flex; align-items: center; gap: .4rem; font-size: .72rem; font-weight: 800; color: #6ee7b7; }
    .rm-live i { width: .55rem; height: .55rem; border-radius: 50%; background: #34d399; box-shadow: 0 0 10px #34d399; animation: livePulse 1.6s ease-in-out infinite; }
    @keyframes livePulse { 0%, 100% { opacity: .5; transform: scale(.8); } 50% { opacity: 1; transform: scale(1.15); } }
    .rm-list { display: grid; gap: .7rem; }
    .rm-card { display: grid; grid-template-columns: auto 1fr auto; gap: .8rem; align-items: center; padding: .8rem .9rem; border-radius: 1.1rem; background: linear-gradient(160deg, rgba(38,32,64,.7), rgba(16,14,28,.6)); border: 1px solid rgba(255,255,255,.13); backdrop-filter: blur(8px); animation: rmIn .45s cubic-bezier(.34,1.4,.64,1) both; }
    @keyframes rmIn { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: none; } }
    .rm-card.live { opacity: .85; }
    .rm-av { display: flex; } .rm-av img { width: 2rem; height: 2rem; border-radius: 50%; border: 2px solid #1b1830; margin-left: -.55rem; background: #14121f; } .rm-av img:first-child { margin-left: 0; }
    .rm-n { font-weight: 900; font-size: 1rem; line-height: 1.2; }
    .rm-m { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .25rem; }
    .chip2 { display: inline-flex; align-items: center; gap: .3rem; font-size: .68rem; font-weight: 800; padding: .12rem .5rem; border-radius: 999px; background: rgba(255,255,255,.08); color: #e2e8f0; }
    .chip2.adult { background: rgba(248,113,113,.15); color: #fca5a5; } .chip2.wait { background: rgba(52,211,153,.15); color: #6ee7b7; } .chip2.play { background: rgba(251,191,36,.15); color: #fcd34d; }
    .rm-j { padding: .6rem 1.1rem; border-radius: .9rem; font-weight: 900; font-size: .85rem; color: #fff; text-decoration: none; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 16px rgba(16,185,129,.35); white-space: nowrap; }
    .rm-j.off { background: rgba(255,255,255,.08); box-shadow: none; color: #94a3b8; }
    .rm-empty { text-align: center; padding: 2.2rem 1rem; border-radius: 1.2rem; border: 1px dashed rgba(255,255,255,.2); color: #cbd5e1; font-weight: 700; }
    .rm-empty small { display: block; color: #94a3b8; margin-top: .3rem; font-weight: 600; }
    .rm-note { margin-top: 1.4rem; font-size: .74rem; color: #94a3b8; font-weight: 600; text-align: center; line-height: 1.5; }
</style>
@endpush

@section('body')
<div class="rm">
    <div class="rm-top"><x-brand /><span class="rm-live"><i></i> Live</span></div>

    <h1>Public games</h1>
    <p class="sub">Jump into a room that's waiting for players, or start your own and let others find it.</p>

    <div class="rm-cta">
        <a class="rm-b go" href="{{ route('setup', ['type' => 'online', 'public' => 1]) }}"><x-icon name="globe" /> Create a public game</a>
        <a class="rm-b" href="{{ route('join') }}"><x-icon name="join" /> Join with a code</a>
        <a class="rm-b" href="{{ route('home') }}">← Home</a>
    </div>

    <div class="rm-list" id="list"></div>
    <div class="rm-empty" id="empty" style="display:none;">No public games right now.<small>Be the first — create one and share the link.</small></div>

    <x-share title="Join a Snakes & Liquor game" text="Come play Snakes & Liquor — a free party board game for your phone." :url="route('rooms.index')" />

    <p class="rm-note">Public rooms show only the room name, the host's name and the avatars — nothing else. Games marked 18+ ask everyone to confirm their age before joining. Any drink can be swapped for water.</p>
</div>

<script>
(function () {
    var rooms = @json($rooms), list = document.getElementById('list'), empty = document.getElementById('empty');
    var base = @json(asset('avatars')), joinUrl = @json(route('join'));
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]; }); };
    var icon = function (n) { return '<svg class="ic" aria-hidden="true"><use href="' + @json(asset('icons.svg')) + '#i-' + n + '"/></svg>'; };
    function ago(m) { return m < 1 ? 'just now' : m < 60 ? m + ' min ago' : Math.floor(m / 60) + ' h ago'; }
    function render() {
        empty.style.display = rooms.length ? 'none' : '';
        list.innerHTML = rooms.map(function (r, i) {
            var avs = (r.avatars || []).map(function (a) { return '<img src="' + base + '/' + esc(a) + '.svg" alt="">'; }).join('');
            var action = r.open && !r.full ? '<a class="rm-j" href="' + joinUrl + '?code=' + encodeURIComponent(r.code) + '">Join</a>'
                : '<span class="rm-j off">' + (r.open ? 'Full' : 'In progress') + '</span>';
            return '<div class="rm-card ' + (r.open ? '' : 'live') + '" style="animation-delay:' + (i * 50) + 'ms">'
                + '<div class="rm-av">' + avs + '</div>'
                + '<div><div class="rm-n">' + esc(r.name) + '</div><div class="rm-m">'
                + '<span class="chip2 ' + (r.open ? 'wait' : 'play') + '">' + (r.open ? 'Waiting for players' : 'Round ' + r.round) + '</span>'
                + '<span class="chip2">' + icon('team') + ' ' + r.players + '/' + r.max + '</span>'
                + '<span class="chip2 ' + (r.mode.adult ? 'adult' : '') + '">' + icon(r.mode.icon) + ' ' + esc(r.mode.label) + (r.mode.adult ? ' · 18+' : '') + '</span>'
                + '<span class="chip2">' + icon(r.intensity.icon) + ' ' + esc(r.intensity.label) + '</span>'
                + (r.host ? '<span class="chip2">Host: ' + esc(r.host) + '</span>' : '') + '<span class="chip2">' + ago(r.minutes) + '</span></div></div>'
                + action + '</div>';
        }).join('');
    }
    render();
    setInterval(function () {
        if (document.hidden) return;
        fetch(@json(route('rooms.list')), { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (d) { rooms = d.rooms; render(); }).catch(function () {});
    }, 5000);
})();
</script>
@endsection
