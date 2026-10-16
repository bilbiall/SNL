<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0b0a16">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ $room->isOnline() ? 'Room ' . $room->join_code : 'Party game' }} — Snakes & Liquor</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    <style>:root { --bw: {{ $board->board_width ?: 1264 }}; --bh: {{ $board->board_height ?: 848 }}; }</style>
    @include('partials.pieces-css')
    @include('partials.play-css')
</head>
<body>
<div class="stage" id="stage">

    {{-- Atmosphere: the board's own art, blurred, behind everything; game pieces pop in and drift in front of it. --}}
    <div class="ambient" style="background-image:url('{{ $board->imageUrl() }}')"></div>
    <div class="vignette"></div>
    <div class="pieces" id="pieces"></div>

    {{-- The board. Tokens are added here by snl-play.js and positioned as % of this box. --}}
    <div class="board-wrap" id="boardWrap">
        <img src="{{ $board->imageUrl() }}" alt="{{ $board->name }} board" draggable="false">
        <div id="glow" class="tile-glow"></div>
    </div>

    <div class="hud hud-tl" id="hudTl"></div>

    <div class="hud hud-tr">
        <button class="icon-btn" id="soundBtn" type="button" aria-label="Sound"><x-icon name="speaker" /></button>
        <button class="icon-btn" id="menuBtn" type="button" aria-label="Menu"><x-icon name="menu" /></button>
    </div>

    <div class="hud hud-left" id="players"></div>

    @if($room->isOnline())
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

    <div class="toasts" id="toasts"></div>

    {{-- Overlays: each is filled by JS --}}
    <div class="ov" id="ovCard"></div>
    <div class="ov" id="ovPrompt"></div>
    <div class="ov" id="ovLobby"></div>
    <div class="ov" id="ovWin"></div>
    <div class="ov" id="ovMenu"></div>
    <div class="ov" id="ovConfirm"></div>
</div>

<script src="{{ asset('js/arcade-sounds.js') }}"></script>
<script>
window.SNL = {
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
    },
    isParty: @json($room->isParty()),
    meId: @json($meId),
    positions: @json($positions),
    state: @json($state),
    mode: @json($mode),
    levels: @json(config('snl.intensities')),
    kinds: @json($tileKinds),
    kindColors: @json(collect(config('snl.tile_kinds'))->map(fn ($k) => $k['color'])),
    powersCfg: @json(config('snl.powers')),
    timing: @json($timing),
    boardName: @json($board->name),
    iconUrl: @json(asset('icons.svg')),
    iconKeys: @json(\App\Support\Icons::keys()),
};
</script>
<script src="{{ asset('js/snl-play.js') }}?v={{ filemtime(public_path('js/snl-play.js')) }}"></script>
</body>
</html>
