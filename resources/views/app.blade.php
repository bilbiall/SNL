@extends('layouts.site')

@section('title', 'Get the app — Snakes & Liquor')

@push('head')
<style>
    body { background: radial-gradient(ellipse at 50% 0%, #2a1550, #0b0a16 65%); min-height: 100vh; }
    .ap { max-width: 34rem; margin: 0 auto; padding: 1.4rem 1.1rem 3rem; text-align: center; }
    .ap-logo { display: inline-flex; align-items: center; gap: .5rem; color: #fff; text-decoration: none; font-weight: 900; }
    .ap-icon { width: 8rem; height: 8rem; border-radius: 1.9rem; margin: 1.6rem auto .9rem; display: block; box-shadow: 0 14px 50px rgba(236,72,153,.5); animation: apFloat 4s ease-in-out infinite; }
    @keyframes apFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
    .ap h1 { font-size: clamp(1.7rem, 7vw, 2.4rem); font-weight: 900; margin: 0; }
    .ap .sub { color: #cbd5e1; font-weight: 600; margin: .4rem 0 1.4rem; }
    .ap-dl { display: flex; align-items: center; justify-content: center; gap: .7rem; padding: 1.1rem 1.4rem; border-radius: 1.2rem; font-size: 1.15rem; font-weight: 900; color: #fff; text-decoration: none; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 12px 34px rgba(16,185,129,.45); transition: transform .15s; }
    .ap-dl:hover { transform: translateY(-3px) scale(1.02); } .ap-dl .ic { font-size: 1.5rem; }
    .ap-dl small { display: block; font-size: .72rem; font-weight: 700; opacity: .85; }
    .ap-card { margin-top: 1.4rem; text-align: left; padding: 1rem 1.1rem; border-radius: 1.1rem; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.13); }
    .ap-card h2 { font-size: .8rem; letter-spacing: .12em; text-transform: uppercase; color: #fbbf24; margin: 0 0 .6rem; font-weight: 900; }
    .ap-card ol { margin: 0; padding-left: 1.2rem; color: #e2e8f0; font-weight: 600; font-size: .86rem; line-height: 1.55; }
    .ap-card li { margin-bottom: .35rem; } .ap-card b { color: #fff; }
    .ap-card p { margin: .4rem 0 0; color: #94a3b8; font-size: .76rem; font-weight: 600; line-height: 1.5; }
    .ap-row { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; margin-top: .9rem; }
    .ap-b { padding: .55rem .95rem; border-radius: .8rem; border: 1px solid rgba(255,255,255,.2); background: rgba(255,255,255,.1); color: #fff; font: 800 .82rem Figtree, system-ui, sans-serif; cursor: pointer; text-decoration: none; }
    .ap-b:hover { background: rgba(255,255,255,.18); }
    .ap-hash { word-break: break-all; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: .66rem; color: #94a3b8; }
    .ap-na { padding: 1rem; border-radius: 1rem; background: rgba(248,113,113,.12); border: 1px solid rgba(248,113,113,.4); font-weight: 700; }
</style>
@endpush

@section('body')
<div class="ap">
    <x-brand />

    <img class="ap-icon" src="{{ asset('icons/icon-512.png') }}" alt="Snakes &amp; Liquor app icon">
    <h1>Get the Android app</h1>
    <p class="sub">Play on one phone — even with no signal. Everything is inside the app.</p>

    @if($apk)
        <a class="ap-dl" id="dl" href="{{ route('app.android') }}" download>
            <x-icon name="phone" />
            <span>Download for Android<small>Version {{ $apk['versionName'] }} · {{ number_format($apk['sizeBytes'] / 1048576, 1) }} MB · .apk</small></span>
        </a>
        <div class="ap-row">
            <button type="button" class="ap-b" id="copyLink">Copy download link</button>
            <button type="button" class="ap-b" id="shareLink" style="display:none;">Send to my phone</button>
        </div>

        <div class="ap-card">
            <h2>How to install</h2>
            <ol>
                <li>Tap <b>Download for Android</b> on your phone.</li>
                <li>Open the downloaded file (<b>Snakes-and-Liquor.apk</b>) from the notification or your <b>Downloads</b> folder.</li>
                <li>If Android asks, allow <b>“Install unknown apps”</b> for your browser — just this once — then tap <b>Install</b>.</li>
                <li>Open <b>Snakes &amp; Liquor</b>. It works with no data and saves your game after every move.</li>
            </ol>
            <p>Android shows this warning because the app isn't from Google Play. It's the same app as this website. You can switch “Install unknown apps” back off afterwards.</p>
        </div>

        <div class="ap-card">
            <h2>Android TV &amp; Fire TV</h2>
            <p style="margin-top:0;">The same app also installs on <b>Android TV, Google TV and Fire TV</b>. On a TV it opens the <b>big-screen table</b>: the TV shows the board, the cards and a QR code, and everyone plays from their own phone. It needs internet.</p>
            <ol>
                <li>On the TV, install the free <b>Downloader</b> app (Fire TV: Appstore · Android TV: Google Play).</li>
                <li>Open Downloader and type <b>{{ preg_replace('#^https?://#', '', rtrim(config('snl.public_url'), '/')) }}/download/tv</b>, then choose <b>Go</b>.</li>
                <li>When it asks, allow Downloader to install apps, then tap <b>Install</b> and <b>Open</b>.</li>
                <li>Start an online game on a phone and enter its room code on the TV (or pick a public game).</li>
            </ol>
            <p style="margin-bottom:0;">Prefer a browser? Open <b>{{ preg_replace('#^https?://#', '', rtrim(config('snl.public_url'), '/')) }}/tv</b> on the TV's web browser — no install needed.</p>
        </div>

        @if(!empty($win))
        <div class="ap-card">
            <h2>Windows PC app</h2>
            <p style="margin-top:0;"><a href="{{ route('app.windows') }}" download style="color:#f9a8d4;font-weight:900;">Download for Windows</a> — Version {{ $win['versionName'] }} · {{ number_format($win['sizeBytes'] / 1048576, 0) }} MB. Run the installer; it plays with no internet. Windows may say “protected your PC” because the app isn't signed: click <b>More info → Run anyway</b>.</p>
        </div>
        @endif

        <div class="ap-card">
            <h2>On a PC or iPhone?</h2>
            <p style="margin-top:0;">The .apk is for Android phones and tablets. On a computer, install the game from the <b>install icon in Chrome or Edge's address bar</b> — it opens in its own window and works offline. On iPhone, tap <b>Share → Add to Home Screen</b>.</p>
        </div>

        <div class="ap-card">
            <h2>File check</h2>
            <p style="margin-top:0;">SHA-256 of the file (to confirm it downloaded intact):</p>
            <div class="ap-hash">{{ $apk['sha256'] }}</div>
        </div>
    @else
        <div class="ap-na">The Android app isn't available to download yet. You can still play in the browser — and install it from Chrome or Edge's address bar.</div>
        <div class="ap-row"><a class="ap-b" href="{{ route('home') }}">Back to the game</a></div>
    @endif

    <p style="margin-top:1.6rem;"><a href="{{ route('home') }}" style="color:#94a3b8;font-weight:700;font-size:.8rem;">← Back to the game</a></p>
</div>
@if($apk)
<script>
(function () {
    var url = location.origin + @json(route('app.android', [], false));
    var copy = document.getElementById('copyLink'), share = document.getElementById('shareLink');
    copy.onclick = function () {
        (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () { copy.textContent = 'Link copied'; }, function () { prompt('Copy this link', url); });
    };
    if (navigator.share) { share.style.display = ''; share.onclick = function () { navigator.share({ title: 'Snakes & Liquor for Android', text: 'Get the Snakes & Liquor app:', url: url }).catch(function () {}); }; }
})();
</script>
@endif
@endsection
