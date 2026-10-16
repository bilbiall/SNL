@extends('gameset.layout')

@section('title', 'Words — GameSet')

@section('gameset')
<div class="mb-4">
    <h1 style="font-weight:900;font-size:1.3rem;margin:0;">Words</h1>
    <p style="font-size:.76rem;color:rgba(255,255,255,.45);margin:.2rem 0 0;max-width:46rem;">
        These fill the <code>{slots}</code> in card text. A card that says <i>"Recommend a movie for {scene}"</i> picks a new scene every time it's played, so the same
        square never feels the same twice. The more words a slot has, the fresher the game. Words can be limited to a mode and an intensity, like cards.
    </p>
</div>

<div class="flex flex-wrap gap-2 mb-5">
    @foreach($slots as $s)
        <a href="{{ route('gameset.words.index', ['slot' => $s]) }}" class="gs-tab {{ $slot === $s ? 'on' : '' }}" style="border:1px solid {{ ($counts[$s] ?? 0) < 6 ? 'rgba(248,113,113,.4)' : 'rgba(255,255,255,.1)' }};">
            {{ '{' . $s . '}' }} <span style="opacity:.6;">· {{ $counts[$s] ?? 0 }}</span>
        </a>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-4 mb-6">
    <div class="gs-box" style="background:rgba(245,158,11,.05);border:1px solid rgba(245,158,11,.18);margin:0;">
        <h2 style="font-weight:900;color:#fbbf24;font-size:.85rem;margin:0 0 .3rem;">Add a word to <code>{{ '{' . $slot . '}' }}</code></h2>
        @if(isset($known[$slot]))<p style="font-size:.7rem;color:rgba(255,255,255,.4);margin:0 0 .6rem;">{{ $known[$slot] }}</p>@endif
        <form method="POST" action="{{ route('gameset.words.store') }}" class="grid grid-cols-3 gap-2">
            @csrf
            <div><label class="gs-h">Slot</label><input name="slot" value="{{ $slot }}" class="gs-in" pattern="[a-z_]{2,24}" title="lowercase letters and underscores" required></div>
            <div><label class="gs-h">Mode</label><select name="mode" class="gs-in">@foreach($modes as $k => $m)<option value="{{ $k }}" {{ $k === 'all' ? 'selected' : '' }}>{{ $m['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Intensity</label><select name="intensity" class="gs-in">@foreach($levels as $n => $lv)<option value="{{ $n }}">{{ $lv['label'] }}</option>@endforeach</select></div>
            <div class="col-span-3"><label class="gs-h">Word or phrase</label><input name="text" class="gs-in" maxlength="90" required></div>
            <div class="col-span-3"><button class="gs-btn go">Add word</button></div>
        </form>
        <p style="font-size:.68rem;color:rgba(255,255,255,.35);margin:.6rem 0 0;">New slot? Type its name in the Slot box (e.g. <code>snack</code>), add a word, then use <code>{snack}</code> in any card.</p>
    </div>

    <div class="gs-box" style="background:rgba(139,92,246,.05);border:1px solid rgba(139,92,246,.2);margin:0;">
        <h2 style="font-weight:900;color:#c4b5fd;font-size:.85rem;margin:0 0 .6rem;">Add many at once <span style="font-weight:600;color:rgba(255,255,255,.4);">(one per line)</span></h2>
        <form method="POST" action="{{ route('gameset.words.bulk') }}" class="grid grid-cols-3 gap-2">
            @csrf
            <input type="hidden" name="slot" value="{{ $slot }}">
            <div class="col-span-2"><label class="gs-h">Mode</label><select name="mode" class="gs-in">@foreach($modes as $k => $m)<option value="{{ $k }}" {{ $k === 'all' ? 'selected' : '' }}>{{ $m['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Intensity</label><select name="intensity" class="gs-in">@foreach($levels as $n => $lv)<option value="{{ $n }}">{{ $lv['label'] }}</option>@endforeach</select></div>
            <div class="col-span-3"><textarea name="lines" rows="6" class="gs-in" placeholder="a rainy Sunday&#10;a Nairobi traffic jam&#10;a first date" required></textarea></div>
            <div class="col-span-3"><button class="gs-btn vio">Add all to {{ '{' . $slot . '}' }}</button></div>
        </form>
    </div>
</div>

<h2 style="font-weight:900;font-size:.9rem;margin:0 0 .6rem;">{{ '{' . $slot . '}' }} words <span style="color:#6b7280;font-weight:700;">({{ $words->count() }})</span></h2>
<div class="space-y-2">
    @forelse($words as $w)
    <form method="POST" action="{{ route('gameset.words.update', $w) }}" class="grid grid-cols-12 gap-2 items-center" style="{{ $w->is_active ? '' : 'opacity:.45;' }}">
        @csrf @method('PUT')
        <input type="hidden" name="slot" value="{{ $w->slot }}">
        <input name="text" value="{{ $w->text }}" class="gs-in col-span-12 sm:col-span-6" maxlength="90" required>
        <select name="mode" class="gs-in col-span-4 sm:col-span-2">@foreach($modes as $k => $m)<option value="{{ $k }}" {{ $w->mode === $k ? 'selected' : '' }}>{{ $m['label'] }}</option>@endforeach</select>
        <select name="intensity" class="gs-in col-span-4 sm:col-span-1">@foreach($levels as $n => $lv)<option value="{{ $n }}" {{ $w->intensity === $n ? 'selected' : '' }}>{{ $lv['label'] }}</option>@endforeach</select>
        <label class="col-span-4 sm:col-span-1 flex items-center gap-1" style="font-size:.7rem;color:#9ca3af;"><input type="checkbox" name="is_active" value="1" {{ $w->is_active ? 'checked' : '' }}> On</label>
        <div class="col-span-12 sm:col-span-2 flex gap-1">
            <button class="gs-btn soft">Save</button>
            <button type="submit" form="delw-{{ $w->id }}" class="gs-btn del" onclick="return confirm('Delete this word?')">Delete</button>
        </div>
    </form>
    <form id="delw-{{ $w->id }}" method="POST" action="{{ route('gameset.words.destroy', $w) }}">@csrf @method('DELETE')</form>
    @empty
        <div class="snl-card" style="text-align:center;color:#9ca3af;">No words in this slot yet.</div>
    @endforelse
</div>
@endsection
