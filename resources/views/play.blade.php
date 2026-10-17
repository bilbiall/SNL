<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0b0a16">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ ($local ?? false) ? 'Party game' : ($room->isOnline() ? 'Room ' . $room->join_code : 'Party game') }} — Snakes & Liquor</title>
    @include('partials.pwa-head')
    <style>:root { --bw: {{ $board->board_width ?: 1264 }}; --bh: {{ $board->board_height ?: 848 }}; }</style>
    @include('partials.pieces-css')
    @include('partials.play-css')
    @include('partials.brand-css')
</head>
<body>
@include('partials.loader', ['wait' => 1])
<div class="stage" id="stage">

    {{-- Atmosphere: the board's own art, blurred, behind everything; game pieces pop in and drift in front of it. --}}
    <div class="ambient" id="ambientBg" style="background-image:url('{{ $board->imageUrl() }}')"></div>
    <div class="vignette"></div>
    <div class="pieces" id="pieces"></div>

    {{-- The board. Tokens are added here by snl-play.js and positioned as % of this box. --}}
    <div class="board-wrap" id="boardWrap">
        <img id="boardImg" src="{{ $board->imageUrl() }}" alt="{{ $board->name }} board" draggable="false">
        <div class="board-dim"></div>
        <div id="glow" class="tile-glow"></div>
    </div>

    {{-- HUD. On phones the two .side wrappers vanish (display:contents) and each piece floats in a corner of the stage.
         On a wide screen they become two matching sidebars: info + players on the left, controls + moves on the right. --}}
    <header class="topnav" id="topnav">
        <x-brand size="nav" />
        <div class="nav-mid" id="navMid"></div>
        <div class="nav-btns">
            @if($music)<button class="icon-btn" id="musicBtn" type="button" aria-label="Music"><x-icon name="music" /></button>@endif
            <button class="icon-btn" id="soundBtn" type="button" aria-label="Sound"><x-icon name="speaker" /></button>
            <button class="icon-btn" id="menuBtn" type="button" aria-label="Menu"><x-icon name="menu" /></button>
        </div>
    </header>

    <div class="side side-l">
        <div class="hud hud-tl" id="hudTl"></div>
        <div class="hud hud-left" id="players"></div>
    </div>

    <div class="side side-r">
        <div class="feed" id="feed" aria-live="polite">
            <div class="feed-h">Recent moves</div>
            <div class="feed-list" id="feedList"></div>
        </div>

        @if(!($local ?? false) && $room->isOnline())
        <div class="hud hud-bl">
            <div class="reactbar" id="reactBar">
                @foreach($reactions as $key)<button type="button" data-react="{{ $key }}" aria-label="React"><x-icon :name="$key" /></button>@endforeach
            </div>
            <button class="icon-btn" id="reactToggle" type="button" aria-label="Reactions"><x-icon name="jester" /></button>
        </div>
        @endif

        <div class="hud hud-br">
            <div class="turn-banner wait" id="turnBanner">…</div>
            <button class="die-badge" id="dieBtn" type="button" disabled aria-label="Roll the dice">
                <span class="die-label">Roll</span>
                <div class="die-scene">
                    <div class="die-cube" id="dieCube">
                        <div class="die-face f1"><i class="pip" style="grid-area:2/2"></i></div>
                        <div class="die-face f2"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:3/3"></i></div>
                        <div class="die-face f3"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:2/2"></i><i class="pip" style="grid-area:3/3"></i></div>
                        <div class="die-face f4"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:1/3"></i><i class="pip" style="grid-area:3/1"></i><i class="pip" style="grid-area:3/3"></i></div>
                        <div class="die-face f5"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:1/3"></i><i class="pip" style="grid-area:2/2"></i><i class="pip" style="grid-area:3/1"></i><i class="pip" style="grid-area:3/3"></i></div>
                        <div class="die-face f6"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:1/3"></i><i class="pip" style="grid-area:2/1"></i><i class="pip" style="grid-area:2/3"></i><i class="pip" style="grid-area:3/1"></i><i class="pip" style="grid-area:3/3"></i></div>
                    </div>
                </div>
                <span class="die-val" id="dieVal"></span>
            </button>
            <div class="powers" id="powers"></div>
        </div>
    </div>

    <div class="watch" id="watch"></div>
    <div class="toasts" id="toasts"></div>

    {{-- Overlays: each is filled by JS --}}
    <div class="ov" id="ovCard"></div>
    <div class="ov" id="ovPrompt"></div>
    <div class="ov" id="ovLobby"></div>
    <div class="ov" id="ovWin"></div>
    <div class="ov" id="ovMenu"></div>
    <div class="ov" id="ovConfirm"></div>
</div>

@if(($local ?? false) || $room->isParty())
@include('partials.avatar-picker')
@endif
<script src="{{ \App\Support\OfflineAssets::js('arcade-sounds.js') }}"></script>
<script src="{{ \App\Support\OfflineAssets::js('snl-audio.js') }}"></script>
<script src="{{ \App\Support\OfflineAssets::js('pwa.js') }}"></script>
<script src="{{ \App\Support\OfflineAssets::js('app-update.js') }}"></script>
@if($local ?? false)
{{-- Local (offline-capable) party table: the game runs in this browser; snl-local.js fills in the saved game, then snl-play.js starts. --}}
<script>
window.SNL = {
    local: true,
    audioBase: @json(asset('audio')), music: @json($music),
    token: 'local', csrf: '',
    packUrl: @json(url('/offline/pack.json')),
    urls: {
        state: 'local:state', roll: 'local:roll', decide: 'local:decide', start: 'local:start', leave: 'local:leave', react: 'local:react', rematch: 'local:rematch', addplayer: 'local:addplayer',
        home: @json(url('/')), setup: @json(route('setup', ['type' => 'party'])), join: '', base: @json(url('/play')),
    },
    isParty: true,
    meId: null,
    positions: null,
    state: null,
    mode: null,
    levels: @json(config('snl.intensities')),
    buzz: @json(config('snl.buzz')),
    memeSounds: @json(\App\Support\ReactionSounds::manifest()),
    memeLevels: @json(\App\Support\ReactionSounds::levels()),
    memeCfg: @json(collect(config('snl.reaction_sounds'))->except('categories')),
    kinds: null,
    kindColors: @json(collect(config('snl.tile_kinds'))->map(fn ($k) => $k['color'])->merge(config('snl.event_colors'))),
    powersCfg: @json(config('snl.powers')),
    timing: @json($timing),
    boardName: '',
    iconUrl: @json(asset('icons.svg')),
    avatarBase: @json(asset('avatars')),
    iconKeys: @json(\App\Support\Icons::keys()),
};
</script>
<script src="{{ \App\Support\OfflineAssets::js('snl-engine.js') }}"></script>
<script src="{{ \App\Support\OfflineAssets::js('snl-local.js') }}"></script>
<script>
SNLLocal.boot(window.SNL).then(function (ok) {
    if (!ok) return;
    var s = document.createElement('script');
    s.src = @json(\App\Support\OfflineAssets::js('snl-play.js'));
    document.body.appendChild(s);
});
</script>
@else
<script>
window.SNL = {
    audioBase: @json(asset('audio')), music: @json($music),
    token: @json($room->token),
    csrf: document.querySelector('meta[name=csrf-token]').content,
    urls: {
        state:   @json(route('play.state', $room->token)),
        roll:    @json(route('play.roll', $room->token)),
        decide:  @json(route('play.decide', $room->token)),
        start:   @json(route('play.start', $room->token)),
        leave:   @json(route('play.leave', $room->token)),
        react:   @json(route('play.react', $room->token)),
        rematch: @json(route('play.rematch', $room->token)),
        home:    @json(route('home')),
        join:    @json(route('join', ['code' => $room->join_code])),
        base:    @json(url('/play')),
        setup:   @json(route('setup', ['type' => 'party'])),
        local:   @json(route('play.local')),
        addplayer: @json(route('play.addplayer', $room->token)),
    },
    packUrl: @json(url('/offline/pack.json')),
    isParty: @json($room->isParty()),
    meId: @json($meId),
    positions: @json($positions),
    state: @json($state),
    mode: @json($mode),
    levels: @json(config('snl.intensities')),
    buzz: @json(config('snl.buzz')),
    memeSounds: @json(\App\Support\ReactionSounds::manifest()),
    memeLevels: @json(\App\Support\ReactionSounds::levels()),
    memeCfg: @json(collect(config('snl.reaction_sounds'))->except('categories')),
    kinds: @json($tileKinds),
    kindColors: @json(collect(config('snl.tile_kinds'))->map(fn ($k) => $k['color'])->merge(config('snl.event_colors'))),
    powersCfg: @json(config('snl.powers')),
    timing: @json($timing),
    boardName: @json($board->name),
    iconUrl: @json(asset('icons.svg')),
    avatarBase: @json(asset('avatars')),
    iconKeys: @json(\App\Support\Icons::keys()),
};
</script>
@if($room->isOnline())
<script src="{{ \App\Support\OfflineAssets::js('snl-engine.js') }}"></script>
<script src="{{ \App\Support\OfflineAssets::js('snl-local.js') }}"></script>
@endif
<script src="{{ \App\Support\OfflineAssets::js('snl-play.js') }}"></script>
@endif
</body>
</html>
