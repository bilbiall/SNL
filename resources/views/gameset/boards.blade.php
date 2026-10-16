@extends('gameset.layout')

@section('title', 'Boards — GameSet')

@push('head')
<style>
    .tile-row { display: grid; grid-template-columns: 46px 150px 70px 1fr 1.4fr; gap: 6px; align-items: center; padding: .4rem .7rem; border-bottom: 1px solid rgba(255,255,255,.04); }
    .tile-row:hover { background: rgba(255,255,255,.02); }
    .tile-num { font-weight: 900; font-size: .8rem; color: #a5b4fc; text-align: center; }
    .row-group { background: rgba(255,255,255,.02); border: 1px solid rgba(255,255,255,.07); border-radius: 1rem; overflow: hidden; margin-bottom: 1rem; }
    .pager { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: space-between; align-items: center; }
    .pager-pages, .pager-tools { display: flex; flex-wrap: wrap; gap: .3rem; align-items: center; }
    .pg { border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.04); color: #cbd5e1; font: inherit; font-size: .72rem; font-weight: 800; padding: .32rem .7rem; border-radius: .6rem; cursor: pointer; }
    .pg.on { background: linear-gradient(135deg, #f59e0b, #ec4899); color: #fff; border-color: transparent; }
    .row-group-h { padding: .55rem .9rem; font-size: .75rem; font-weight: 900; color: #fbbf24; background: rgba(245,158,11,.06); border-bottom: 1px solid rgba(245,158,11,.15); }
    @media (max-width: 900px) { .tile-row { grid-template-columns: repeat(2, 1fr); } .tile-head { display: none; } }

    .layout-tab { font-size: 11.5px; font-weight: 800; padding: .5rem 1rem; border-radius: .7rem .7rem 0 0; cursor: pointer; background: rgba(255,255,255,.03); color: #9ca3af; border: 1px solid rgba(255,255,255,.08); border-bottom: none; }
    .layout-tab.active { background: rgba(245,158,11,.1); color: #fbbf24; border-color: rgba(245,158,11,.3); }
    .layout-pane { display: none; }
    .layout-pane.active { display: block; }

    .cal-tile {
        position: absolute; width: 4.6%; aspect-ratio: 1; max-width: 32px; min-width: 18px; transform: translate(-50%,-50%);
        background: rgba(245,158,11,.28); border: 1.5px solid rgba(245,158,11,.85); border-radius: 5px; display: flex; align-items: center; justify-content: center;
        font-size: 9px; font-weight: 900; color: #fff; cursor: grab; user-select: none; touch-action: none; z-index: 5;
    }
    .cal-tile:hover { background: rgba(245,158,11,.5); z-index: 6; }
    .cal-tile.dragging { background: rgba(139,92,246,.6); border-color: rgba(139,92,246,.9); cursor: grabbing; z-index: 7; }

    /* A faithful simulation of the live forced-landscape phone view: the same 90° rotation the play page applies, at a
       representative phone size. Tiles are dragged and stored in the SAME % space as the board image inside the rotator. */
    .phone-sim-outer { width: 380px; height: 660px; margin: 0 auto; position: relative; overflow: hidden; border-radius: 1.25rem; background: #0b0a16; border: 1px solid rgba(255,255,255,.1); }
    .phone-sim-rotator { position: absolute; top: 50%; left: 50%; width: 660px; height: 380px; margin-top: -190px; margin-left: -330px; transform: rotate(90deg); display: flex; align-items: center; justify-content: center; container-type: size; }
    /* Same sizing rule as the live stage: the largest board-shaped box that fits the rotated screen. */
    .phone-sim-rotator .board-wrap-mobile { position: relative; flex: none; width: min(100cqw, calc(100cqh * var(--bw) / var(--bh))); aspect-ratio: var(--bw) / var(--bh); }
    .phone-sim-rotator img { width: 100%; height: 100%; display: block; object-fit: fill; }
    .cal-tile-mobile { position: absolute; width: 4.6%; aspect-ratio: 1; max-width: 26px; min-width: 16px; transform: translate(-50%,-50%); background: rgba(236,72,153,.28); border: 1.5px solid rgba(236,72,153,.85); border-radius: 5px; display: flex; align-items: center; justify-content: center; cursor: grab; user-select: none; touch-action: none; z-index: 5; }
    .cal-tile-mobile:hover { background: rgba(236,72,153,.5); z-index: 6; }
    .cal-tile-mobile.dragging { background: rgba(139,92,246,.6); border-color: rgba(139,92,246,.9); cursor: grabbing; z-index: 7; }
    .cal-tile-mobile span { display: block; transform: rotate(-90deg); font-size: 8px; font-weight: 900; color: #fff; }
</style>
@endpush

@section('gameset')
{{-- Board switcher --}}
<div class="flex flex-wrap items-center gap-2 mb-5">
    @foreach($boards as $b)
        <a href="{{ route('gameset.boards.index', ['board' => $b->id]) }}" class="layout-tab"
           style="border-bottom:1px solid rgba(255,255,255,.08);border-radius:.7rem;text-decoration:none;{{ $board && $b->id === $board->id ? 'background:rgba(245,158,11,.12);color:#fbbf24;border-color:rgba(245,158,11,.4);' : '' }}">
            {{ $b->name }} <span style="opacity:.55;font-weight:700;">· {{ $b->tile_count }}</span>@unless($b->is_active) <span style="color:#f87171;">· hidden</span>@endunless
        </a>
    @endforeach
    <button type="button" onclick="document.getElementById('newBoardPanel').classList.toggle('hidden')" class="gs-btn ok">➕ New board</button>
</div>

<div id="newBoardPanel" class="hidden gs-box" style="background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.2);">
    <h2 style="font-weight:900;color:#6ee7b7;font-size:.9rem;margin:0 0 .3rem;">➕ Add a board</h2>
    <p style="font-size:.72rem;color:rgba(255,255,255,.45);margin:0 0 .8rem;">Upload the artwork and say how many numbered squares it has. The board starts <b>hidden from players</b> with the squares laid out in a rough zig-zag — drag each onto the art, set what it does, then tick <b>Active</b>.</p>
    <form method="POST" action="{{ route('gameset.boards.store') }}" enctype="multipart/form-data" class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end">
        @csrf
        <div class="col-span-2"><label class="gs-h">Board name</label><input name="name" class="gs-in" placeholder="e.g. Nairobi Nights" required maxlength="60"></div>
        <div><label class="gs-h">Squares</label><input name="tile_count" type="number" min="10" max="200" value="60" class="gs-in" required></div>
        <div class="col-span-2"><label class="gs-h">Board art (JPG/PNG/WebP ≤ 8 MB)</label><input name="image" type="file" accept="image/png,image/jpeg,image/webp" class="gs-in" required></div>
        <button class="gs-btn ok">Create</button>
    </form>
</div>

@if(!$board)
    <div class="snl-card" style="text-align:center;">No boards yet — add one above.</div>
@else
<style>:root { --bw: {{ $board->board_width ?: 1264 }}; --bh: {{ $board->board_height ?: 848 }}; }</style>

{{-- Board layout calibration --}}
<div class="flex items-center justify-between mb-2">
    <h2 style="font-weight:900;color:#fbbf24;font-size:.9rem;margin:0;">Board layout — drag each square onto its real spot</h2>
    <button type="submit" form="board-calibrator" id="saveLayoutBtn" class="gs-btn go">Save board layout</button>
</div>

{{-- Quick layout: click the first and last square of each row and the rest are spaced evenly. --}}
<div class="gs-box" style="background:rgba(236,72,153,.05);border:1px solid rgba(236,72,153,.2);padding:.9rem 1.1rem;margin-bottom:1rem;">
    <div class="flex flex-wrap items-center gap-2">
        <b style="color:#f9a8d4;font-size:.85rem;">Quick layout</b>
        <span style="font-size:.72rem;color:rgba(255,255,255,.5);">Squares in each row, bottom row first (comma-separated):</span>
        <input id="qlCounts" class="gs-in" style="max-width:340px;" autocomplete="off">
        <button type="button" id="qlStart" class="gs-btn go">Start</button>
        <button type="button" id="qlCancel" class="gs-btn soft" style="display:none;">Cancel</button>
    </div>
    <p id="qlStatus" style="font-size:.8rem;margin:.55rem 0 0;color:#fbcfe8;font-weight:700;"></p>
    <p style="font-size:.7rem;color:rgba(255,255,255,.4);margin:.3rem 0 0;">Count the squares in every row of your art first — the counts must add up to {{ $board->tile_count }}. Then click the <b>centre of the first square</b> and the <b>centre of the last square</b> of each row, in numbering order (a snaking board flips direction every row — just follow the numbers shown). The squares in between are spaced evenly. Works on whichever layout tab is open; then nudge anything that's off by dragging, and press Save.</p>
</div>

<div class="flex gap-1" style="margin-bottom:-1px;">
    <div class="layout-tab active" data-tab="desktop" onclick="switchLayoutTab('desktop')">Desktop layout</div>
    <div class="layout-tab" data-tab="mobile" onclick="switchLayoutTab('mobile')">Mobile landscape layout</div>
</div>

<form id="board-calibrator" method="POST" action="{{ route('gameset.boards.positions', $board) }}">
    @csrf
    <input type="hidden" name="layout" id="layoutField" value="desktop">

    <div id="desktopPane" class="layout-pane active">
        <div id="boardCalibrator" class="rounded-2xl overflow-hidden mb-2" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);position:relative;">
            <img src="{{ $board->imageUrl() }}" alt="{{ $board->name }}" class="w-full h-auto block" draggable="false">
            @foreach($tiles->sortBy('number') as $t)
                <div class="cal-tile" data-number="{{ $t->number }}" title="{{ $kinds[$t->kind]['label'] ?? $t->kind }}"
                     style="left:{{ $t->pos_left ?? 50 }}%; top:{{ $t->pos_top ?? 50 }}%;">{{ $t->number }}</div>
            @endforeach
        </div>
        <p style="font-size:.72rem;color:rgba(255,255,255,.4);">Drag any numbered square onto the board square it belongs to, then press <b>Save board layout</b>. This places the tokens on desktop and landscape phones.</p>
    </div>

    <div id="mobilePane" class="layout-pane">
        <div class="phone-sim-outer mb-2">
            <div class="phone-sim-rotator">
                <div id="boardCalibratorMobile" class="board-wrap-mobile">
                    <img src="{{ $board->imageUrl() }}" alt="" draggable="false">
                    @foreach($tiles->sortBy('number') as $t)
                        <div class="cal-tile-mobile" data-number="{{ $t->number }}"
                             style="left:{{ $t->pos_left_mobile ?? $t->pos_left ?? 50 }}%; top:{{ $t->pos_top_mobile ?? $t->pos_top ?? 50 }}%;"><span>{{ $t->number }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>
        <p style="font-size:.72rem;color:rgba(255,255,255,.4);">A faithful simulation of the rotated, forced-landscape view phones use in portrait. Calibrate here only if squares look off on a phone held upright; leave it alone to reuse the desktop positions (shown pre-filled).</p>
    </div>
    <div id="hiddenInputs"></div>
</form>

{{-- Board settings --}}
<div class="gs-box mt-6" style="background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.18);">
    <h2 style="font-weight:900;color:#a5b4fc;font-size:.9rem;margin:0 0 .8rem;">Board settings — {{ $board->name }}</h2>
    <form method="POST" action="{{ route('gameset.boards.settings', $board) }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
        @csrf
        <div class="col-span-2"><label class="gs-h">Board name</label><input name="name" value="{{ $board->name }}" class="gs-in" required maxlength="60"></div>
        <label class="flex items-center gap-2" style="font-size:.78rem;font-weight:800;color:#d1d5db;"><input type="checkbox" name="is_active" value="1" {{ $board->is_active ? 'checked' : '' }} style="width:1.1rem;height:1.1rem;"> Active (players can pick it)</label>
        <button class="gs-btn vio">Save settings</button>
    </form>

    <div class="grid sm:grid-cols-2 gap-4 mt-4 pt-4" style="border-top:1px solid rgba(255,255,255,.07);">
        <form method="POST" action="{{ route('gameset.boards.art', $board) }}" enctype="multipart/form-data">
            @csrf
            <label class="gs-h">Replace board art <span style="text-transform:none;letter-spacing:0;">(square positions are kept)</span></label>
            <div class="flex items-center gap-2 mt-1">
                <img src="{{ $board->imageUrl() }}" alt="" style="height:38px;border-radius:6px;">
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="gs-in" required>
                <button class="gs-btn soft">Upload</button>
            </div>
        </form>
        <form method="POST" action="{{ route('gameset.boards.resize', $board) }}" onsubmit="return confirm('Change this board to ' + this.tile_count.value + ' squares? Extra squares are dropped (or added blank) and any snake/ladder pointing past the end is cleared.')">
            @csrf
            <label class="gs-h">Number of squares <span style="text-transform:none;letter-spacing:0;">(blocked while a game is running)</span></label>
            <div class="flex items-center gap-2 mt-1">
                <input type="number" name="tile_count" min="10" max="200" value="{{ $board->tile_count }}" class="gs-in" required>
                <button class="gs-btn soft">Resize</button>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('gameset.boards.destroy', $board) }}" class="mt-4" onsubmit="return confirm('Delete this board, its art and all its squares? This cannot be undone.')">
        @csrf @method('DELETE')
        <button class="gs-btn del">Delete this board</button>
        <span style="font-size:.72rem;color:rgba(255,255,255,.4);margin-left:.5rem;">{{ $roomCount ? "It has {$roomCount} games on record — it can only be retired (untick Active)." : 'Never played — safe to delete.' }}</span>
    </form>
</div>

{{-- Square registry --}}
<form method="POST" action="{{ route('gameset.boards.tiles', $board) }}">
    @csrf
    <div class="flex items-center justify-between mb-2">
        <h2 style="font-weight:900;color:#fbbf24;font-size:.9rem;margin:0;">Squares ({{ $tiles->count() }})</h2>
        <button class="gs-btn go">Save all squares</button>
    </div>
    <p style="font-size:.72rem;color:rgba(255,255,255,.42);margin:0 0 .8rem;">
        <b>Type</b> decides what happens on landing (hover the dots on the board for a quick look). <b>Target</b> is only for snakes (must be a <i>lower</i> square) and ladders (a <i>higher</i> square).
        <b>Text</b> is optional — leave it blank to draw a random card from the deck for the room's mode; fill it in to make this square always say the same thing (e.g. a sponsor line).
        <b>Icon</b> is shown on the card pop-up.
    </p>

    <div class="pager" id="tilePager">
        <div class="pager-pages" id="pagerPages">
            @foreach($groups as $i => $g)<button type="button" class="pg" data-page="{{ $i }}">{{ $loop->first ? 1 : $i * 10 + 1 }}–{{ min(($i + 1) * 10, $board->tile_count) }}</button>@endforeach
            <button type="button" class="pg" data-page="all">All</button>
        </div>
        <div class="pager-tools">
            <select id="pagerKind" class="gs-in" style="width:auto;"><option value="">Every type</option>@foreach($kinds as $key => $k)<option value="{{ $key }}">{{ $k['label'] }}</option>@endforeach</select>
            <input id="pagerJump" type="number" min="1" max="{{ $board->tile_count }}" class="gs-in" style="width:5.5rem;" placeholder="Go to #">
            <button type="button" class="pg" id="pagerPrev">‹ Prev</button><button type="button" class="pg" id="pagerNext">Next ›</button>
        </div>
    </div>
    <div id="pagerNote" style="font-size:.7rem;color:rgba(255,255,255,.4);margin:.2rem 0 .7rem;"></div>

    @foreach($groups as $g)
    <div class="row-group" data-group="{{ $loop->index }}">
        <div class="row-group-h">{{ $g['label'] }}</div>
        <div class="tile-row tile-head" style="background:rgba(255,255,255,.03);">
            <span class="gs-h">#</span><span class="gs-h">Type</span><span class="gs-h">Target</span><span class="gs-h">Icon</span><span class="gs-h">Fixed text (optional)</span>
        </div>
        @foreach($g['tiles'] as $t)
        <div class="tile-row" data-n="{{ $t->number }}" data-kind="{{ $t->kind }}">
            <input type="hidden" name="tiles[{{ $t->number }}][number]" value="{{ $t->number }}">
            <span class="tile-num">{{ $t->number }}</span>
            <select name="tiles[{{ $t->number }}][kind]" class="gs-in">
                @foreach($kinds as $key => $k)
                    <option value="{{ $key }}" {{ $t->kind === $key ? 'selected' : '' }}>{{ $k['label'] }}</option>
                @endforeach
            </select>
            <input type="number" name="tiles[{{ $t->number }}][target_number]" value="{{ $t->target_number }}" min="1" max="{{ $board->tile_count }}" class="gs-in" placeholder="→">
            <input name="tiles[{{ $t->number }}][icon]" value="{{ $t->icon }}" class="gs-in" maxlength="24" list="iconKeys" placeholder="icon" title="A game-icon name such as flag, trophy or duel — leave blank to use the square type's own icon">
            <input name="tiles[{{ $t->number }}][label]" value="{{ $t->label }}" class="gs-in" maxlength="120" placeholder="{{ ($kinds[$t->kind]['card'] ?? null) ? 'Random from the ' . ($kinds[$t->kind]['label']) . ' deck (' . ($deckCounts[$kinds[$t->kind]['card']] ?? 0) . ' cards)' : 'No card on this square type' }}">
        </div>
        @endforeach
    </div>
    @endforeach
    <button class="gs-btn go">Save all squares</button>
</form>
<script>
(function () {
    var groups = [].slice.call(document.querySelectorAll('.row-group[data-group]'));
    var kindSel = document.getElementById('pagerKind'), jump = document.getElementById('pagerJump'), note = document.getElementById('pagerNote');
    var buttons = [].slice.call(document.querySelectorAll('#pagerPages .pg'));
    var page = 0; try { page = sessionStorage.getItem('snlTilePage') || 0; } catch (e) {}
    if (page !== 'all') page = Math.min(+page || 0, groups.length - 1);
    function render() {
        var kind = kindSel.value, shown = 0;
        groups.forEach(function (g, i) {
            var inPage = page === 'all' || +page === i, any = false;
            g.querySelectorAll('.tile-row[data-n]').forEach(function (r) {
                var ok = inPage && (!kind || r.dataset.kind === kind);
                r.style.display = ok ? '' : 'none'; if (ok) { any = true; shown++; }
            });
            var head = g.querySelector('.tile-head'); if (head) head.style.display = any ? '' : 'none';
            g.style.display = any ? '' : 'none';
        });
        buttons.forEach(function (b) { b.classList.toggle('on', b.dataset.page === String(page)); });
        note.textContent = shown + ' square' + (shown === 1 ? '' : 's') + ' shown' + (kind ? ' (filtered by type)' : '') + ' — every square is still saved when you press Save.';
        try { sessionStorage.setItem('snlTilePage', page); } catch (e) {}
    }
    buttons.forEach(function (b) { b.addEventListener('click', function () { page = b.dataset.page === 'all' ? 'all' : +b.dataset.page; render(); }); });
    document.getElementById('pagerPrev').addEventListener('click', function () { page = page === 'all' ? 0 : Math.max(0, page - 1); render(); });
    document.getElementById('pagerNext').addEventListener('click', function () { page = page === 'all' ? 0 : Math.min(groups.length - 1, page + 1); render(); });
    kindSel.addEventListener('change', function () { if (kindSel.value && page !== 'all') page = 'all'; render(); });
    jump.addEventListener('change', function () { var n = +jump.value; if (n > 0) { page = Math.floor((n - 1) / 10); render(); var row = document.querySelector('.tile-row[data-n="' + n + '"]'); if (row) { row.scrollIntoView({ block: 'center' }); row.style.outline = '2px solid #f59e0b'; setTimeout(function () { row.style.outline = ''; }, 1600); } } });
    render();
})();
</script>

<datalist id="iconKeys">@foreach(\App\Support\Icons::keys() as $iconKey)<option value="{{ $iconKey }}">@endforeach</datalist>

<script>
    // Drag-to-place on one calibrator board — wired once per pane (desktop, mobile).
    function wire(boardId, tileSel, toPercent) {
        const board = document.getElementById(boardId);
        if (!board) return;
        board.querySelectorAll(tileSel).forEach(tile => {
            tile.addEventListener('pointerdown', e => { tile.classList.add('dragging'); try { tile.setPointerCapture(e.pointerId); } catch (_) {} e.preventDefault(); });
            tile.addEventListener('pointermove', e => {
                if (!tile.classList.contains('dragging')) return;
                const { left, top } = toPercent(board, e.clientX, e.clientY);
                tile.style.left = left.toFixed(2) + '%'; tile.style.top = top.toFixed(2) + '%';
            });
            tile.addEventListener('pointerup', () => tile.classList.remove('dragging'));
            tile.addEventListener('pointercancel', () => tile.classList.remove('dragging'));
        });
    }
    const clamp = v => Math.max(0, Math.min(100, v));
    const convDesktop = (b, x, y) => { const r = b.getBoundingClientRect(); return { left: clamp((x - r.left) / r.width * 100), top: clamp((y - r.top) / r.height * 100) }; };
    // The mobile pane is rotated 90° clockwise, so a pointer's screen position maps to the board's % space
    // rotated too: local "left%" runs down the screen's Y axis, local "top%" runs backwards along X.
    const convMobile = (b, x, y) => { const r = b.getBoundingClientRect(); return { left: clamp((y - r.top) / r.height * 100), top: clamp(100 - (x - r.left) / r.width * 100) }; };
    wire('boardCalibrator', '.cal-tile', convDesktop);
    wire('boardCalibratorMobile', '.cal-tile-mobile', convMobile);

    // ── Quick layout: click each row's first and last square; the squares between are spaced evenly. ──
    const PANES = {
        desktop: { id: 'boardCalibrator', sel: '.cal-tile', conv: convDesktop },
        mobile:  { id: 'boardCalibratorMobile', sel: '.cal-tile-mobile', conv: convMobile },
    };
    function spaceRow(a, b, count) {
        return Array.from({ length: count }, (_, k) => count === 1 ? { left: a.left, top: a.top } : {
            left: a.left + (b.left - a.left) * k / (count - 1),
            top:  a.top  + (b.top  - a.top)  * k / (count - 1),
        });
    }
    (function quickLayout() {
        const total = {{ $board->tile_count }};
        const input = document.getElementById('qlCounts'), status = document.getElementById('qlStatus');
        const startBtn = document.getElementById('qlStart'), cancelBtn = document.getElementById('qlCancel');
        const sum = a => a.reduce((s, n) => s + n, 0);
        let counts = [], clicks = [], row = 0, active = null;

        // A sensible default: the same grid shape new boards start with.
        (function () {
            const cols = Math.max(4, Math.ceil(Math.sqrt(total * 1.5)));
            const out = []; let left = total;
            while (left > 0) { out.push(Math.min(cols, left)); left -= cols; }
            input.value = out.join(',');
        })();

        function stop(msg) {
            active = null;
            document.querySelectorAll('.cal-board').forEach(b => { b.classList.remove('picking'); b.style.cursor = ''; });
            startBtn.disabled = false; cancelBtn.style.display = 'none';
            status.textContent = msg || '';
        }
        function ask() {
            const first = sum(counts.slice(0, row)) + 1, last = first + counts[row] - 1;
            const firstClick = clicks.length % 2 === 0;
            status.innerHTML = `Row ${row + 1} of ${counts.length} — click the <b>centre of square ${firstClick ? first : last}</b> (${firstClick ? 'first' : 'last'} square of this row).`;
        }
        startBtn.onclick = () => {
            counts = input.value.split(/[\s,]+/).map(Number).filter(n => n > 0);
            if (!counts.length || sum(counts) !== total) { status.textContent = `Those add up to ${sum(counts)}, but this board has ${total} squares.`; return; }
            active = PANES[document.getElementById('layoutField').value];
            clicks = []; row = 0;
            const board = document.getElementById(active.id);
            board.classList.add('picking'); board.style.cursor = 'crosshair';
            startBtn.disabled = true; cancelBtn.style.display = '';
            ask();
        };
        cancelBtn.onclick = () => stop('Cancelled — nothing was moved after the last finished row.');

        Object.values(PANES).forEach(p => {
            const board = document.getElementById(p.id);
            board.classList.add('cal-board');
            // Capture phase: while picking, a click is a measurement, never the start of a drag.
            board.addEventListener('pointerdown', e => {
                if (active !== p) return;
                e.preventDefault(); e.stopPropagation();
                clicks.push(p.conv(board, e.clientX, e.clientY));
                if (clicks.length % 2 === 1) { ask(); return; }
                const first = sum(counts.slice(0, row)) + 1;
                const pts = spaceRow(clicks[clicks.length - 2], clicks[clicks.length - 1], counts[row]);
                pts.forEach((pt, k) => {
                    const tile = board.querySelector(`${p.sel}[data-number="${first + k}"]`);
                    if (tile) { tile.style.left = pt.left.toFixed(2) + '%'; tile.style.top = pt.top.toFixed(2) + '%'; }
                });
                row++;
                if (row >= counts.length) stop('All rows placed — nudge any square that is off by dragging it, then press Save.');
                else { clicks = []; ask(); }
            }, true);
        });
    })();

    function switchLayoutTab(name) {
        document.querySelectorAll('.layout-tab[data-tab]').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
        document.getElementById('desktopPane').classList.toggle('active', name === 'desktop');
        document.getElementById('mobilePane').classList.toggle('active', name === 'mobile');
        document.getElementById('layoutField').value = name;
        document.getElementById('saveLayoutBtn').textContent = name === 'mobile' ? 'Save mobile landscape layout' : 'Save board layout';
    }

    // Only the active pane's positions are submitted, so calibrating one layout never touches the other.
    document.getElementById('board-calibrator').addEventListener('submit', () => {
        const mobile = document.getElementById('layoutField').value === 'mobile';
        const pane = document.getElementById(mobile ? 'boardCalibratorMobile' : 'boardCalibrator');
        const box = document.getElementById('hiddenInputs');
        box.innerHTML = '';
        pane.querySelectorAll(mobile ? '.cal-tile-mobile' : '.cal-tile').forEach(tile => {
            const n = tile.dataset.number;
            [['number', n], ['pos_left', (parseFloat(tile.style.left) || 50).toFixed(2)], ['pos_top', (parseFloat(tile.style.top) || 50).toFixed(2)]].forEach(([f, v]) => {
                const i = document.createElement('input'); i.type = 'hidden'; i.name = `positions[${n}][${f}]`; i.value = v; box.appendChild(i);
            });
        });
    });
</script>
@endif
@endsection
