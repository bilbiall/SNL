@extends('layouts.site')

@section('title', ($type === 'party' ? 'New party game' : 'New online room') . ' — Snakes & Liquor')

@section('body')
<div class="snl-narrow">
    <header class="flex items-center justify-between py-3 mb-2">
        <a href="{{ route('home') }}" class="logo text-lg"><x-icon name="snake" /> Snakes <b>&amp;</b> Liquor <x-icon name="cheers" /></a>
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
                <span>Everyone playing is <b>18 or over</b>. <span class="snl-muted font-semibold">Any drink can be swapped for water — it's your game, your pace.</span></span>
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
                <p class="snl-muted text-xs mt-2">Tap a colour dot to change it. Names can be silly.</p>
            @else
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="snl-label" for="host_name">Your name</label>
                        <input class="snl-input" id="host_name" name="host_name" value="{{ old('host_name') }}" maxlength="24" placeholder="e.g. Amina" required autocomplete="off">
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

        <button class="btn btn-primary btn-block" style="font-size:1.05rem;padding:1rem;">
            <x-icon :name="$type === 'party' ? 'dice' : 'globe'" /> {{ $type === 'party' ? 'Start the game' : 'Create room' }}
        </button>
    </form>

    <footer class="snl-foot">Drink responsibly. Never drive after drinking.</footer>
</div>

<script>
(function () {
    const modes = @json($modes);
    const form = document.getElementById('setupForm');

    // Mode blurb + 18+ confirmation only when the chosen mode needs it
    function syncMode() {
        const r = form.querySelector('input[name=content_mode]:checked');
        document.getElementById('modeBlurb').textContent = r ? modes[r.value].blurb : '';
        document.getElementById('adultBox').style.display = (r && r.dataset.adult === '1') ? 'flex' : 'none';
    }
    form.querySelectorAll('input[name=content_mode]').forEach(i => i.addEventListener('change', syncMode));
    syncMode();

    @if($type === 'party')
    const colors = @json($colors);
    const avatars = @json($avatars);
    const iconUrl = @json(asset('icons.svg'));
    const ico = (k) => '<svg class="ic" aria-hidden="true"><use href="' + iconUrl + '#i-' + k + '"/></svg>';
    const limits = @json($limits);
    const rows = document.getElementById('playerRows');
    const oldNames = @json(array_values(old('names', [])));
    const oldColors = @json(array_values(old('colors', [])));
    let count = Math.max(limits.min, Math.min(limits.max, oldNames.length || 4));
    const names = oldNames.slice();
    const picked = oldColors.slice();

    function render() {
        // keep whatever was typed
        rows.querySelectorAll('input[name="names[]"]').forEach((el, i) => names[i] = el.value);
        rows.innerHTML = '';
        for (let i = 0; i < count; i++) {
            const color = picked[i] || colors[i % colors.length];
            picked[i] = color;
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2';
            row.innerHTML = `
                <button type="button" class="dot" data-i="${i}" title="Change colour"
                    style="width:2.3rem;height:2.3rem;border-radius:50%;border:2px solid #fff;background:${color};flex-shrink:0;font-size:1.15rem;cursor:pointer;color:#fff;display:flex;align-items:center;justify-content:center;padding:0;">${ico(avatars[i % avatars.length])}</button>
                <input type="hidden" name="colors[]" value="${color}">
                <input class="snl-input" name="names[]" maxlength="24" placeholder="Player ${i + 1}" value="${(names[i] || '').replace(/"/g, '&quot;')}" autocomplete="off" required>`;
            rows.appendChild(row);
        }
        document.getElementById('countLabel').textContent = count + ' players';
        document.getElementById('minusBtn').disabled = count <= limits.min;
        document.getElementById('plusBtn').disabled = count >= limits.max;
    }
    rows.addEventListener('click', e => {
        const dot = e.target.closest('.dot'); if (!dot) return;
        const i = +dot.dataset.i;
        const next = (colors.indexOf(picked[i]) + 1) % colors.length;
        picked[i] = colors[next];
        rows.querySelectorAll('input[name="names[]"]').forEach((el, k) => names[k] = el.value);
        render();
    });
    document.getElementById('plusBtn').onclick = () => { if (count < limits.max) { count++; render(); } };
    document.getElementById('minusBtn').onclick = () => { if (count > limits.min) { count--; render(); } };
    render();
    @endif
})();
</script>
@endsection
