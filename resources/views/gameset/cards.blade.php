@extends('gameset.layout')

@section('title', 'Cards — GameSet')

@section('gameset')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 style="font-weight:900;font-size:1.3rem;margin:0;">🃏 Cards</h1>
        <p style="font-size:.76rem;color:rgba(255,255,255,.45);margin:.2rem 0 0;">The deck players draw from when they land on a square. Each content mode has its own deck, and each card has an intensity — a room only draws cards up to the intensity it was set to.</p>
    </div>
</div>

{{-- Mode tabs with live counts --}}
<div class="flex flex-wrap gap-2 mb-4">
    @foreach($modes as $key => $m)
        @php $n = ($counts[$key] ?? collect())->sum(); @endphp
        <a href="{{ route('gameset.cards.index', ['mode' => $key]) }}" class="gs-tab {{ $mode === $key ? 'on' : '' }}" style="border:1px solid rgba(255,255,255,.1);">
            {{ $m['emoji'] }} {{ $m['label'] }} <span style="opacity:.6;">· {{ $n }}</span>@unless($m['enabled']) <span style="opacity:.5;">· off</span>@endunless
        </a>
    @endforeach
</div>

{{-- Coverage: which kinds are well-stocked for this mode --}}
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('gameset.cards.index', ['mode' => $mode]) }}" class="gs-tab {{ !$kind ? 'on' : '' }}" style="border:1px solid rgba(255,255,255,.08);">All kinds</a>
    @foreach($kinds as $k => $label)
        @php $c = ($counts[$mode] ?? collect())[$k] ?? 0; @endphp
        <a href="{{ route('gameset.cards.index', ['mode' => $mode, 'kind' => $k]) }}" class="gs-tab {{ $kind === $k ? 'on' : '' }}" style="border:1px solid {{ $c < 3 ? 'rgba(248,113,113,.4)' : 'rgba(255,255,255,.08)' }};">
            {{ $label }} <span style="opacity:.6;">· {{ $c }}</span>
        </a>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-4 mb-6">
    {{-- Add one --}}
    <div class="gs-box" style="background:rgba(245,158,11,.05);border:1px solid rgba(245,158,11,.18);margin:0;">
        <h2 style="font-weight:900;color:#fbbf24;font-size:.85rem;margin:0 0 .6rem;">➕ Add a card</h2>
        <form method="POST" action="{{ route('gameset.cards.store') }}" class="grid grid-cols-2 gap-2">
            @csrf
            <div><label class="gs-h">Kind</label><select name="kind" class="gs-in">@foreach($kinds as $k => $l)<option value="{{ $k }}" {{ $kind === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
            <div><label class="gs-h">Mode</label><select name="mode" class="gs-in">@foreach($modes as $k => $m)<option value="{{ $k }}" {{ $mode === $k ? 'selected' : '' }}>{{ $m['emoji'] }} {{ $m['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Intensity</label><select name="intensity" class="gs-in">@foreach($levels as $n => $lv)<option value="{{ $n }}" {{ $n === 2 ? 'selected' : '' }}>{{ $lv['emoji'] }} {{ $lv['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Only on board</label><select name="board_id" class="gs-in"><option value="">Every board</option>@foreach($boards as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
            <div class="col-span-2"><label class="gs-h">Card text</label><input name="text" class="gs-in" maxlength="220" placeholder="e.g. Choose a friend to take a shot." required></div>
            <div class="col-span-2"><button class="gs-btn go">Add card</button></div>
        </form>
    </div>

    {{-- Bulk --}}
    <div class="gs-box" style="background:rgba(139,92,246,.05);border:1px solid rgba(139,92,246,.2);margin:0;">
        <h2 style="font-weight:900;color:#c4b5fd;font-size:.85rem;margin:0 0 .6rem;">📋 Add many at once <span style="font-weight:600;color:rgba(255,255,255,.4);">(one card per line)</span></h2>
        <form method="POST" action="{{ route('gameset.cards.bulk') }}" class="grid grid-cols-3 gap-2">
            @csrf
            <div><label class="gs-h">Kind</label><select name="kind" class="gs-in">@foreach($kinds as $k => $l)<option value="{{ $k }}" {{ $kind === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
            <div><label class="gs-h">Mode</label><select name="mode" class="gs-in">@foreach($modes as $k => $m)<option value="{{ $k }}" {{ $mode === $k ? 'selected' : '' }}>{{ $m['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Intensity</label><select name="intensity" class="gs-in">@foreach($levels as $n => $lv)<option value="{{ $n }}" {{ $n === 2 ? 'selected' : '' }}>{{ $lv['label'] }}</option>@endforeach</select></div>
            <div class="col-span-3"><textarea name="lines" rows="5" class="gs-in" placeholder="Take a shot.&#10;Pick someone to take two sips.&#10;Everybody cheers!" required></textarea></div>
            <div class="col-span-3"><button class="gs-btn vio">Add all</button></div>
        </form>
    </div>
</div>

{{-- The deck --}}
<h2 style="font-weight:900;font-size:.9rem;margin:0 0 .6rem;">{{ $modes[$mode]['emoji'] }} {{ $modes[$mode]['label'] }} deck{{ $kind ? ' — ' . $kinds[$kind] : '' }} <span style="color:#6b7280;font-weight:700;">({{ $cards->count() }})</span></h2>
<div class="space-y-2">
    @forelse($cards as $c)
    <form method="POST" action="{{ route('gameset.cards.update', $c) }}" class="grid grid-cols-12 gap-2 items-center" style="{{ $c->is_active ? '' : 'opacity:.45;' }}">
        @csrf @method('PUT')
        <select name="kind" class="gs-in col-span-6 sm:col-span-2">@foreach($kinds as $k => $l)<option value="{{ $k }}" {{ $c->kind === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
        <select name="intensity" class="gs-in col-span-3 sm:col-span-1">@foreach($levels as $n => $lv)<option value="{{ $n }}" {{ $c->intensity === $n ? 'selected' : '' }}>{{ $lv['emoji'] }}</option>@endforeach</select>
        <input type="hidden" name="mode" value="{{ $c->mode }}">
        <select name="board_id" class="gs-in col-span-3 sm:col-span-2"><option value="">Every board</option>@foreach($boards as $b)<option value="{{ $b->id }}" {{ $c->board_id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach</select>
        <input name="text" value="{{ $c->text }}" class="gs-in col-span-12 sm:col-span-4" maxlength="220" required>
        <label class="col-span-4 sm:col-span-1 flex items-center gap-1" style="font-size:.7rem;color:#9ca3af;"><input type="checkbox" name="is_active" value="1" {{ $c->is_active ? 'checked' : '' }}> On</label>
        <div class="col-span-8 sm:col-span-2 flex gap-1">
            <button class="gs-btn soft">Save</button>
            <button type="submit" form="del-{{ $c->id }}" class="gs-btn del" onclick="return confirm('Delete this card?')">✕</button>
        </div>
    </form>
    <form id="del-{{ $c->id }}" method="POST" action="{{ route('gameset.cards.destroy', $c) }}">@csrf @method('DELETE')</form>
    @empty
        <div class="snl-card" style="text-align:center;color:#9ca3af;">No cards here yet — add some above. Until then the game falls back to a small built-in line.</div>
    @endforelse
</div>
@endsection
