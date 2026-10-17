@extends('gameset.layout')

@section('title', 'Cards — GameSet')

@section('gameset')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 style="font-weight:900;font-size:1.3rem;margin:0;">Cards</h1>
        <p style="font-size:.76rem;color:rgba(255,255,255,.45);margin:.2rem 0 0;max-width:46rem;">
            The deck players draw from when they land on a square. Each content mode has its own deck, plus an <b>All modes</b> deck that works everywhere.
            A room only draws cards up to the intensity it was set to (Chill → Normal → Spicy).
            Use <code>{you}</code> / <code>{other}</code> for players at the table and <code>{scene}</code>, <code>{vibe}</code>… for word-list slots
            (<a href="{{ route('gameset.words.index') }}" style="color:#fbbf24;">Words</a>) — so one card reads differently every time.
        </p>
    </div>
</div>

{{-- Mode tabs with live counts --}}
<div class="flex flex-wrap gap-2 mb-4">
    @foreach($modes as $key => $m)
        @php $n = ($counts[$key] ?? collect())->sum(); @endphp
        <a href="{{ route('gameset.cards.index', ['mode' => $key]) }}" class="gs-tab {{ $mode === $key ? 'on' : '' }}" style="border:1px solid rgba(255,255,255,.1);">
            <x-icon :name="$m['icon']" /> {{ $m['label'] }} <span style="opacity:.6;">· {{ $n }}</span>@if(!$m['enabled']) <span style="opacity:.5;">· off</span>@endif
        </a>
    @endforeach
</div>

{{-- Coverage: which kinds are well-stocked for this mode --}}
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('gameset.cards.index', ['mode' => $mode]) }}" class="gs-tab {{ !$kind ? 'on' : '' }}" style="border:1px solid rgba(255,255,255,.08);">All kinds</a>
    @foreach($kinds as $k => $label)
        @php $c = ($counts[$mode] ?? collect())[$k] ?? 0; @endphp
        <a href="{{ route('gameset.cards.index', ['mode' => $mode, 'kind' => $k]) }}" class="gs-tab {{ $kind === $k ? 'on' : '' }}" style="border:1px solid {{ $c < 3 ? 'rgba(248,113,113,.4)' : 'rgba(255,255,255,.08)' }};">
            {{ \Illuminate\Support\Str::before($label, ' (') }} <span style="opacity:.6;">· {{ $c }}</span>
        </a>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-4 mb-6">
    {{-- Add one --}}
    <div class="gs-box" style="background:rgba(245,158,11,.05);border:1px solid rgba(245,158,11,.18);margin:0;">
        <h2 style="font-weight:900;color:#fbbf24;font-size:.85rem;margin:0 0 .6rem;">Add a card</h2>
        <form method="POST" action="{{ route('gameset.cards.store') }}" class="grid grid-cols-2 gap-2" id="addCard">
            @csrf
            <div><label class="gs-h">Kind</label><select name="kind" class="gs-in kind-sel">@foreach($kinds as $k => $l)<option value="{{ $k }}" {{ $kind === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
            <div><label class="gs-h">Mode</label><select name="mode" class="gs-in">@foreach($modes as $k => $m)<option value="{{ $k }}" {{ $mode === $k ? 'selected' : '' }}>{{ $m['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Intensity</label><select name="intensity" class="gs-in">@foreach($levels as $n => $lv)<option value="{{ $n }}" {{ $n === 2 ? 'selected' : '' }}>{{ $lv['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Only on board</label><select name="board_id" class="gs-in"><option value="">Every board</option>@foreach($boards as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
            <div class="col-span-2"><label class="gs-h">Card text</label><input name="text" class="gs-in" maxlength="220" placeholder="e.g. Recommend a movie for {scene}." required></div>
            <div class="only-fact col-span-2 grid grid-cols-2 gap-2" style="display:none;">
                <div><label class="gs-h">Correct answer</label><select name="answer" class="gs-in"><option value="">— pick —</option><option value="true">True</option><option value="false">False</option></select></div>
                <div><label class="gs-h">Explanation (shown after)</label><input name="explain" class="gs-in" maxlength="220"></div>
            </div>
            <div class="only-pen" style="display:none;"><label class="gs-h">Penalty (1 or 2 units)</label><select name="penalty" class="gs-in"><option value="1">1 — a shot / sip</option><option value="2">2 — double</option></select></div>
            <div class="only-spot" style="display:none;"><label class="gs-h">Timer (seconds)</label><input type="number" name="timer" min="5" max="90" value="15" class="gs-in"></div>
            <div class="col-span-2"><button class="gs-btn go">Add card</button></div>
        </form>
    </div>

    {{-- Bulk --}}
    <div class="gs-box" style="background:rgba(139,92,246,.05);border:1px solid rgba(139,92,246,.2);margin:0;">
        <h2 style="font-weight:900;color:#c4b5fd;font-size:.85rem;margin:0 0 .6rem;">Add many at once <span style="font-weight:600;color:rgba(255,255,255,.4);">(one card per line)</span></h2>
        <form method="POST" action="{{ route('gameset.cards.bulk') }}" class="grid grid-cols-3 gap-2" id="bulkCard">
            @csrf
            <div><label class="gs-h">Kind</label><select name="kind" class="gs-in kind-sel">@foreach($kinds as $k => $l)<option value="{{ $k }}" {{ $kind === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
            <div><label class="gs-h">Mode</label><select name="mode" class="gs-in">@foreach($modes as $k => $m)<option value="{{ $k }}" {{ $mode === $k ? 'selected' : '' }}>{{ $m['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Intensity</label><select name="intensity" class="gs-in">@foreach($levels as $n => $lv)<option value="{{ $n }}" {{ $n === 2 ? 'selected' : '' }}>{{ $lv['label'] }}</option>@endforeach</select></div>
            <div class="only-pen" style="display:none;"><label class="gs-h">Penalty</label><select name="penalty" class="gs-in"><option value="1">1 unit</option><option value="2">2 units</option></select></div>
            <div class="only-spot" style="display:none;"><label class="gs-h">Timer (s)</label><input type="number" name="timer" min="5" max="90" value="15" class="gs-in"></div>
            <div class="col-span-3">
                <textarea name="lines" rows="6" class="gs-in" placeholder="Take a shot.&#10;Pick someone to take two sips.&#10;Everybody cheers!" required></textarea>
                <p class="only-fact" style="display:none;font-size:.68rem;color:#fbbf24;margin:.3rem 0 0;">True-or-False lines: <code>T | statement | optional explanation</code> or <code>F | statement | …</code></p>
            </div>
            <div class="col-span-3"><button class="gs-btn vio">Add all</button></div>
        </form>
    </div>
</div>

{{-- The deck --}}
<h2 style="font-weight:900;font-size:.9rem;margin:0 0 .6rem;"><x-icon :name="$modes[$mode]['icon']" /> {{ $modes[$mode]['label'] }} deck{{ $kind ? ' — ' . \Illuminate\Support\Str::before($kinds[$kind], ' (') : '' }} <span style="color:#6b7280;font-weight:700;">({{ $cards->count() }})</span></h2>
<div class="space-y-3">
    @forelse($cards as $c)
    <form method="POST" action="{{ route('gameset.cards.update', $c) }}" class="grid grid-cols-12 gap-2 items-center" style="{{ $c->is_active ? '' : 'opacity:.45;' }}padding-bottom:.5rem;border-bottom:1px solid rgba(255,255,255,.05);">
        @csrf @method('PUT')
        <select name="kind" class="gs-in col-span-6 sm:col-span-2 kind-sel">@foreach($kinds as $k => $l)<option value="{{ $k }}" {{ $c->kind === $k ? 'selected' : '' }}>{{ \Illuminate\Support\Str::before($l, ' (') }}</option>@endforeach</select>
        <select name="intensity" class="gs-in col-span-3 sm:col-span-1">@foreach($levels as $n => $lv)<option value="{{ $n }}" {{ $c->intensity === $n ? 'selected' : '' }}>{{ $lv['label'] }}</option>@endforeach</select>
        <input type="hidden" name="mode" value="{{ $c->mode }}">
        <select name="board_id" class="gs-in col-span-3 sm:col-span-2"><option value="">Every board</option>@foreach($boards as $b)<option value="{{ $b->id }}" {{ $c->board_id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach</select>
        <input name="text" value="{{ $c->text }}" class="gs-in col-span-12 sm:col-span-4" maxlength="220" required>
        <label class="col-span-4 sm:col-span-1 flex items-center gap-1" style="font-size:.7rem;color:#9ca3af;"><input type="checkbox" name="is_active" value="1" {{ $c->is_active ? 'checked' : '' }}> On</label>
        <div class="col-span-8 sm:col-span-2 flex gap-1">
            <button class="gs-btn soft">Save</button>
            <button type="submit" form="del-{{ $c->id }}" class="gs-btn del" onclick="return confirm('Delete this card?')">Delete</button>
        </div>
        {{-- extras, only for the kinds that use them --}}
        <div class="only-fact col-span-12 grid grid-cols-12 gap-2" style="{{ $c->kind === 'fact' ? '' : 'display:none;' }}">
            <select name="answer" class="gs-in col-span-4 sm:col-span-2"><option value="true" {{ $c->answer === true ? 'selected' : '' }}>Answer: True</option><option value="false" {{ $c->answer === false ? 'selected' : '' }}>Answer: False</option></select>
            <input name="explain" value="{{ $c->explain }}" placeholder="Explanation shown after the reveal" class="gs-in col-span-8 sm:col-span-10" maxlength="220">
        </div>
        <div class="only-pen col-span-6 sm:col-span-3" style="{{ in_array($c->kind, ['fact','bluff','spotlight','quickfire'], true) ? '' : 'display:none;' }}"><select name="penalty" class="gs-in"><option value="1" {{ $c->penalty == 1 ? 'selected' : '' }}>Penalty: 1 unit</option><option value="2" {{ $c->penalty == 2 ? 'selected' : '' }}>Penalty: 2 units</option></select></div>
        <div class="only-spot col-span-6 sm:col-span-3" style="{{ in_array($c->kind, ['spotlight','quickfire'], true) ? '' : 'display:none;' }}"><input type="number" name="timer" value="{{ $c->timer ?? 15 }}" min="5" max="90" class="gs-in" title="Seconds on the clock"></div>
    </form>
    <form id="del-{{ $c->id }}" method="POST" action="{{ route('gameset.cards.destroy', $c) }}">@csrf @method('DELETE')</form>
    @empty
        <div class="snl-card" style="text-align:center;color:#9ca3af;">No cards here yet — add some above. Until then the game falls back to a small built-in line.</div>
    @endforelse
</div>

<script>
    // Show only the fields a card kind uses: answer + explanation (True or false), penalty (fact / bluff / spotlight), timer (spotlight).
    function syncKind(scope) {
        var k = scope.querySelector('.kind-sel').value;
        scope.querySelectorAll('.only-fact').forEach(function (e) { e.style.display = k === 'fact' ? '' : 'none'; });
        scope.querySelectorAll('.only-pen').forEach(function (e) { e.style.display = (k === 'fact' || k === 'bluff' || k === 'spotlight' || k === 'quickfire') ? '' : 'none'; });
        scope.querySelectorAll('.only-spot').forEach(function (e) { e.style.display = (k === 'spotlight' || k === 'quickfire') ? '' : 'none'; });
    }
    document.querySelectorAll('form').forEach(function (f) {
        var sel = f.querySelector('.kind-sel'); if (!sel) return;
        sel.addEventListener('change', function () { syncKind(f); }); syncKind(f);
    });
</script>
@endsection
