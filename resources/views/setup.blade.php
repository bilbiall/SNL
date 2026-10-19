@extends('layouts.site')

@section('robots', 'noindex,nofollow')
@section('title', ($type === 'party' ? 'New party game' : 'New online room') . ' — Snakes & Liquor')

@push('head')
@include('partials.ads')
<script src="{{ \App\Support\OfflineAssets::js('snl-ads.js') }}"></script>
@endpush

@section('body')
<div class="snl-narrow">
    <header class="flex items-center justify-between py-3 mb-2">
        <x-brand />
        <a href="{{ route('setup', ['type' => $type === 'party' ? 'online' : 'party']) }}" class="btn btn-ghost btn-sm">
            <x-icon :name="$type === 'party' ? 'globe' : 'phone'" /> {{ $type === 'party' ? 'Online instead' : 'Party mode instead' }}
        </a>
    </header>

    <h1 class="snl-h text-2xl mb-1"><x-icon :name="$type === 'party' ? 'phone' : 'globe'" /> {{ $type === 'party' ? 'New party game' : 'New online room' }}</h1>
    <p class="snl-muted text-sm mb-5">
        {{ $type === 'party'
            ? 'One device on the table — add everyone who is playing and pass it around.'
            : 'You get a 5-letter code to share. Friends join on their own phones with just a name.' }}
    </p>

    @if($type === 'online')
        <form action="{{ route('join') }}" method="GET" class="snl-card flex items-center gap-2 mb-5" style="padding:.7rem .9rem;">
            <x-icon name="join" />
            <span class="text-sm" style="flex:1;font-weight:700;">Got a code from a friend?</span>
            <input name="code" class="snl-input" placeholder="CODE" maxlength="8" autocomplete="off" aria-label="Room code" style="width:7.5rem;text-align:center;text-transform:uppercase;letter-spacing:.2em;font-weight:900;">
            <button class="btn btn-ghost btn-sm">Join</button>
        </form>
    @endif

    @if($errors->any())
        <div class="snl-alert err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <form method="POST" action="{{ route('rooms.store') }}" id="setupForm" class="space-y-5">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        {{-- Vibe --}}
        <div class="snl-card">
            <span class="snl-label">What are we playing?</span>
            <div class="grid grid-cols-3 gap-2">
                @foreach($modes as $key => $m)
                <label class="pill-opt">
                    <input type="radio" name="content_mode" value="{{ $key }}" data-adult="{{ $m['adult'] ? 1 : 0 }}"
                           {{ old('content_mode', 'drinks') === $key ? 'checked' : '' }} {{ $m['enabled'] ? '' : 'disabled' }}>
                    <span><x-icon :name="$m['icon']" style="font-size:1.7rem;" />{{ $m['label'] }}
                        <small>{{ $m['enabled'] ? ($m['adult'] ? '18+ only' : 'Everyone') : 'Coming soon' }}</small></span>
                </label>
                @endforeach
            </div>
            <p class="snl-muted text-xs mt-2" id="modeBlurb"></p>

            <label id="adultBox" class="flex items-start gap-2 mt-3 text-sm font-bold" style="display:none;">
                <input type="checkbox" name="adult" value="1" {{ old('adult') ? 'checked' : '' }} class="mt-1" style="width:1.1rem;height:1.1rem;">
                <span>Everyone playing is <b>18 or over</b>. <span class="snl-muted font-semibold" id="adultNote">Any drink can be swapped for water — it's your game, your pace.</span></span>
            </label>
        </div>

        {{-- Intensity --}}
        <div class="snl-card">
            <span class="snl-label">How wild?</span>
            <div class="grid grid-cols-3 gap-2">
                @foreach($levels as $n => $lv)
                <label class="pill-opt">
                    <input type="radio" name="intensity" value="{{ $n }}" {{ (int) old('intensity', 2) === $n ? 'checked' : '' }}>
                    <span><x-icon :name="$lv['icon']" style="font-size:1.6rem;" />{{ $lv['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Board --}}
        @if($boards->count() > 1)
        <div class="snl-card">
            <span class="snl-label">Pick a board</span>
            <div class="grid grid-cols-2 gap-2">
                @foreach($boards as $b)
                <label class="pill-opt">
                    <input type="radio" name="board_id" value="{{ $b->id }}" {{ (int) old('board_id', $boards->first()->id) === $b->id ? 'checked' : '' }}>
                    <span><img src="{{ $b->imageUrl() }}" alt="" style="width:100%;height:70px;object-fit:cover;border-radius:.6rem;margin-bottom:.3rem;">{{ $b->name }}<small>{{ $b->tile_count }} squares</small></span>
                </label>
                @endforeach
            </div>
        </div>
        @else
            <input type="hidden" name="board_id" value="{{ $boards->first()->id }}">
        @endif

        {{-- Blocks (shields): how many each player starts with --}}
        @php $maxShields = (int) config('snl.powers.shield.max', 5); @endphp
        <div class="snl-card">
            <span class="snl-label">Blocks — how many does each player start with?</span>
            <div class="flex items-center justify-between gap-3">
                <p class="snl-muted text-xs" style="flex:1;margin:0;"><x-icon name="shield" /> A block cancels one card aimed at you — no shot, no forfeit. Everyone starts with the same number; more can be won on Power-up squares and free gifts.</p>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-ghost btn-sm" id="shMinus" aria-label="Fewer blocks">−</button>
                    <b id="shLabel" style="min-width:3.2rem;text-align:center;font-size:1rem;">1</b>
                    <button type="button" class="btn btn-ghost btn-sm" id="shPlus" aria-label="More blocks">+</button>
                </div>
            </div>
            <input type="hidden" name="start_shields" id="startShields" value="{{ (int) old('start_shields', config('snl.powers.shield.start', 1)) }}">
        </div>

        {{-- Players --}}
        <div class="snl-card">
            @if($type === 'party')
                <div class="flex items-center justify-between mb-3">
                    <span class="snl-label" style="margin:0;">Who's playing?</span>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn btn-ghost btn-sm" id="minusBtn" aria-label="Fewer players">−</button>
                        <b id="countLabel" style="min-width:5.5rem;text-align:center;font-size:.85rem;">4 players</b>
                        <button type="button" class="btn btn-ghost btn-sm" id="plusBtn" aria-label="More players">+</button>
                    </div>
                </div>
                <div id="playerRows" class="space-y-2"></div>
                <p class="snl-muted text-xs mt-2">Tap an avatar to choose a look and skin shade. Names can be silly.</p>
            @else
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="snl-label" for="host_name">Your name</label>
                        <div class="flex items-center gap-3">
                            <button type="button" class="avbtn" id="hostAvatarBtn" title="Choose your avatar"><img id="hostAvatarImg" alt=""></button>
                            <input type="hidden" name="avatar" id="hostAvatar" value="{{ old('avatar', $avatarDefaults[0]) }}">
                            <input class="snl-input" id="host_name" name="host_name" value="{{ old('host_name') }}" maxlength="24" placeholder="e.g. Amina" required autocomplete="off">
                        </div>
                    </div>
                    <div>
                        <label class="snl-label" for="max_players">Max players</label>
                        <select class="snl-input" id="max_players" name="max_players">
                            @for($i = $limits['min']; $i <= $limits['max']; $i++)
                                <option value="{{ $i }}" {{ (int) old('max_players', 8) === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            @endif
        </div>

        @if($type === 'online')
        {{-- Public room: listed on /rooms so anyone can find and join it --}}
        @php $wantPublic = (bool) old('public', request('public')); @endphp
        <div class="snl-card">
            <label class="flex items-start gap-3" style="cursor:pointer;">
                <input type="checkbox" name="public" value="1" id="pubBox" {{ $wantPublic ? 'checked' : '' }} style="width:1.2rem;height:1.2rem;margin-top:.15rem;">
                <span class="text-sm" style="font-weight:800;"><x-icon name="globe" /> Make it a public game<br><span class="snl-muted" style="font-weight:600;font-size:.76rem;">Show it on the Public games page so other people can find and join while it's waiting. Leave it off to keep it code-only.</span></span>
            </label>
            <div id="pubName" style="display:none;margin-top:.7rem;">
                <label class="snl-label" for="room_name">Give your game a name</label>
                <input class="snl-input" id="room_name" name="room_name" value="{{ old('room_name') }}" maxlength="40" placeholder="e.g. Friday Night Showdown" autocomplete="off">
                <p class="snl-muted text-xs mt-1">Everyone browsing will see this name, your name and your avatar — keep it friendly (no links).</p>
            </div>
        </div>
        @endif

        <button class="btn btn-primary btn-block" style="font-size:1.05rem;padding:1rem;">
            <x-icon :name="$type === 'party' ? 'dice' : 'globe'" /> {{ $type === 'party' ? 'Start the game' : 'Create room' }}
        </button>
    </form>

    <footer class="snl-foot">Drink responsibly. Never drive after drinking.</footer>
</div>

@include('partials.avatar-picker')

@if($type === 'party')
<script src="{{ \App\Support\OfflineAssets::js('snl-engine.js') }}"></script>
<script src="{{ \App\Support\OfflineAssets::js('snl-local.js') }}"></script>
@endif
<script>
(function () {
    const modes = @json($modes);
    const form = document.getElementById('setupForm');

    // Mode blurb + 18+ confirmation only when the chosen mode needs it
    // blocks (shields) each player starts with
    (function () {
        const max = {{ (int) config('snl.powers.shield.max', 5) }}, input = document.getElementById('startShields'), label = document.getElementById('shLabel');
        const paint = () => { label.textContent = input.value; document.getElementById('shMinus').disabled = +input.value <= 0; document.getElementById('shPlus').disabled = +input.value >= max; };
        document.getElementById('shMinus').onclick = () => { input.value = Math.max(0, +input.value - 1); paint(); };
        document.getElementById('shPlus').onclick = () => { input.value = Math.min(max, +input.value + 1); paint(); };
        paint();
    })();

    function syncMode() {
        const r = form.querySelector('input[name=content_mode]:checked');
        document.getElementById('modeBlurb').textContent = r ? modes[r.value].blurb : '';
        document.getElementById('adultBox').style.display = (r && r.dataset.adult === '1') ? 'flex' : 'none';
        document.getElementById('adultNote').textContent = (r && r.value === 'lovers')
            ? "Everything is optional — skip any card you're not comfortable with."
            : "Any drink can be swapped for water — it's your game, your pace.";
    }
    form.querySelectorAll('input[name=content_mode]').forEach(i => i.addEventListener('change', syncMode));
    syncMode();

    @if($type === 'party')
    const colors = @json($colors);
    const defaults = @json($avatarDefaults);
    const limits = @json($limits);
    const rows = document.getElementById('playerRows');
    const oldNames = @json(array_values(old('names', [])));
    const oldAvatars = @json(array_values(old('avatars', [])));
    let count = Math.max(limits.min, Math.min(limits.max, oldNames.length || 4));
    const names = oldNames.slice();
    const avs = oldAvatars.slice();

    function keepNames() { rows.querySelectorAll('input[name="names[]"]').forEach((el, i) => names[i] = el.value); }
    function render() {
        keepNames();
        rows.innerHTML = '';
        for (let i = 0; i < count; i++) {
            avs[i] = avs[i] || defaults[i % defaults.length];
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3';
            row.style.marginBottom = '.9rem';
            row.innerHTML = `
                <button type="button" class="avbtn" data-i="${i}" title="Choose avatar"><img src="${SNLAvatars.url(avs[i])}" alt=""></button>
                <input type="hidden" name="avatars[]" value="${avs[i]}">
                <input type="hidden" name="colors[]" value="${colors[i % colors.length]}">
                <input class="snl-input" name="names[]" maxlength="24" placeholder="Player ${i + 1}" value="${(names[i] || '').replace(/"/g, '&quot;')}" autocomplete="off" required>`;
            rows.appendChild(row);
        }
        document.getElementById('countLabel').textContent = count + ' players';
        document.getElementById('minusBtn').disabled = count <= limits.min;
        document.getElementById('plusBtn').disabled = count >= limits.max;
    }
    rows.addEventListener('click', e => {
        const b = e.target.closest('.avbtn'); if (!b) return;
        const i = +b.dataset.i;
        keepNames();
        SNLAvatars.open(avs[i], key => { avs[i] = key; render(); });
    });
    document.getElementById('plusBtn').onclick = () => { if (count < limits.max) { count++; render(); } };
    document.getElementById('minusBtn').onclick = () => { if (count > limits.min) { count--; render(); } };
    render();

    // Party games run on this device (works with no signal): build the game here instead of posting to the server.
    const showErr = (msg) => {
        let box = document.getElementById('localErr');
        if (!box) { box = document.createElement('div'); box.id = 'localErr'; box.className = 'snl-alert err'; form.parentNode.insertBefore(box, form); }
        box.textContent = msg;
        box.scrollIntoView({ block: 'center' });
    };
    if (typeof SNLLocal !== 'undefined') SNLLocal.loadPack(); // make sure the game files are on the device
    form.addEventListener('submit', async (e) => {
        if (typeof SNLLocal === 'undefined' || !SNLLocal.hasEngine()) return; // old browser → the server path below still works
        e.preventDefault();
        const fd = new FormData(form);
        const mode = fd.get('content_mode');
        if (modes[mode] && modes[mode].adult && !fd.get('adult')) { showErr('Please confirm that everyone playing is 18 or over for this mode.'); return; }
        const nm = fd.getAll('names[]'), co = fd.getAll('colors[]'), av = fd.getAll('avatars[]');
        if (window.SNLAds) await SNLAds.show('new_game'); // a pause: the host is starting a game
        if (window.SNLLoader) SNLLoader.show();
        try {
            await SNLLocal.create({ boardId: +fd.get('board_id'), mode: mode, intensity: +fd.get('intensity'), startShields: fd.get('start_shields'), entries: nm.map((n, i) => ({ name: n, color: co[i], avatar: av[i] })) });
            location.href = @json(route('play.local'));
        } catch (err) {
            if (navigator.onLine && !(err && err.name === 'GameError')) { form.submit(); return; }
            if (window.SNLLoader) SNLLoader.hide();
            showErr((err && err.message) || 'Could not start the game.');
        }
    });
    @else
    const pubBox = document.getElementById('pubBox'), pubName = document.getElementById('pubName'), roomName = document.getElementById('room_name');
    const syncPub = () => { pubName.style.display = pubBox.checked ? 'block' : 'none'; roomName.required = pubBox.checked; if (pubBox.checked && !roomName.value) roomName.focus({ preventScroll: true }); };
    pubBox.addEventListener('change', syncPub); syncPub();
    form.addEventListener('submit', async (e) => {
        if (form.dataset.adDone || !window.SNLAds || !form.checkValidity()) return;
        e.preventDefault();
        await SNLAds.show('new_room'); // a pause: the host is creating a room
        form.dataset.adDone = '1';
        form.submit();
    });
    const hostAv = document.getElementById('hostAvatar'), hostImg = document.getElementById('hostAvatarImg');
    const showHost = () => { hostImg.src = SNLAvatars.url(hostAv.value); };
    document.getElementById('hostAvatarBtn').onclick = () => SNLAvatars.open(hostAv.value, key => { hostAv.value = key; showHost(); });
    showHost();
    @endif
})();
</script>
@endsection
