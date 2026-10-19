@extends('gameset.layout')

@section('title', 'Packs — GameSet')

@php
    $status = function ($p) {
        $today = now()->toDateString();
        if (!$p->is_active) return ['Off', '#9ca3af'];
        if ($p->starts_on && $p->starts_on->toDateString() > $today) return ['Scheduled', '#a5b4fc'];
        if ($p->ends_on && $p->ends_on->toDateString() < $today) return ['Ended', '#fca5a5'];
        return ['Live', '#6ee7b7'];
    };
@endphp

@section('gameset')
<div class="mb-4">
    <h1 style="font-weight:900;font-size:1.3rem;margin:0;">Packs</h1>
    <p style="font-size:.76rem;color:rgba(255,255,255,.45);margin:.2rem 0 0;max-width:48rem;">
        A pack is a named set of cards you can switch on for a period: a sponsor's pack ("Presented by …" shows on each card and the
        sponsor's logo appears on the TV screen), or a seasonal one (December, Valentine's). Cards come out of the pack only while it is
        <b>Live</b> (switched on and inside its dates) and only in the modes you tick. Leave the dates empty to keep it on.
    </p>
</div>

<div class="gs-box" style="background:rgba(245,158,11,.05);border:1px solid rgba(245,158,11,.18);">
    <h2 style="font-weight:900;color:#fbbf24;font-size:.85rem;margin:0 0 .6rem;">New pack</h2>
    <form method="POST" action="{{ route('gameset.packs.store') }}" enctype="multipart/form-data" class="grid grid-cols-12 gap-2">
        @csrf
        @include('gameset._pack-fields', ['p' => null, 'modes' => $modes])
        <div class="col-span-12"><button class="gs-btn go">Create pack</button></div>
    </form>
</div>

@forelse($packs as $p)
@php [$label, $color] = $status($p); @endphp
<div class="gs-box" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.1);">
    <div style="display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;margin-bottom:.7rem;">
        @if($p->logo)<img src="{{ asset(ltrim($p->logo, '/')) }}" alt="" style="height:2.2rem;max-width:6rem;object-fit:contain;border-radius:.4rem;background:rgba(255,255,255,.08);padding:.2rem;">@endif
        <h2 style="font-weight:900;font-size:1rem;margin:0;">{{ $p->name }}</h2>
        <span style="font-size:.66rem;font-weight:900;padding:.15rem .55rem;border-radius:999px;border:1px solid {{ $color }};color:{{ $color }};">{{ $label }}</span>
        <span style="font-size:.72rem;color:#9ca3af;">{{ $p->cards_count }} cards{{ $p->sponsor ? ' · Presented by ' . $p->sponsor : '' }}</span>
    </div>

    <form method="POST" action="{{ route('gameset.packs.update', $p) }}" enctype="multipart/form-data" class="grid grid-cols-12 gap-2 mb-3">
        @csrf @method('PUT')
        @include('gameset._pack-fields', ['p' => $p, 'modes' => $modes])
        <label class="col-span-6 sm:col-span-3 flex items-center gap-1" style="font-size:.74rem;color:#d1d5db;"><input type="checkbox" name="is_active" value="1" {{ $p->is_active ? 'checked' : '' }}> Switched on</label>
        <div class="col-span-12 flex gap-2"><button class="gs-btn ok">Save pack</button></div>
    </form>
    <form method="POST" action="{{ route('gameset.packs.destroy', $p) }}" onsubmit="return confirm('Delete this pack and all {{ $p->cards_count }} of its cards?');" class="mb-3">
        @csrf @method('DELETE')<button class="gs-btn del">Delete pack</button>
    </form>

    <details style="margin-top:.4rem;">
        <summary style="cursor:pointer;font-weight:800;font-size:.8rem;color:#c4b5fd;">Add cards to this pack (one per line)</summary>
        <form method="POST" action="{{ route('gameset.packs.cards.store', $p) }}" class="grid grid-cols-3 gap-2 mt-2">
            @csrf
            <div><label class="gs-h">Kind</label><select name="kind" class="gs-in">@foreach($kinds as $k => $l)<option value="{{ $k }}">{{ \Illuminate\Support\Str::before($l, ' (') }}</option>@endforeach</select></div>
            <div><label class="gs-h">Mode</label><select name="mode" class="gs-in"><option value="all">All modes</option>@foreach($modes as $k => $m)<option value="{{ $k }}">{{ $m['label'] }}</option>@endforeach</select></div>
            <div><label class="gs-h">Intensity</label><select name="intensity" class="gs-in">@foreach($levels as $n => $lv)<option value="{{ $n }}">{{ $lv['label'] }}</option>@endforeach</select></div>
            <div class="col-span-3"><textarea name="lines" rows="5" class="gs-in" placeholder="Sip your drink if you have been to Kilimanjaro&#10;Everyone name a snack you can buy at {{ $p->sponsor ?: 'the venue' }}" required></textarea></div>
            <div class="col-span-3"><button class="gs-btn vio">Add cards</button></div>
        </form>
    </details>

    @if($p->cards->isNotEmpty())
    <details style="margin-top:.6rem;">
        <summary style="cursor:pointer;font-weight:800;font-size:.8rem;color:#9ca3af;">Show the {{ $p->cards->count() }} cards</summary>
        <div class="space-y-1 mt-2">
            @foreach($p->cards as $c)
            <div style="display:flex;gap:.5rem;align-items:center;font-size:.76rem;">
                <span style="color:#6b7280;min-width:7.5rem;">{{ $kinds[$c->kind] ?? $c->kind }} · {{ $c->mode }} · {{ $levels[$c->intensity]['label'] ?? $c->intensity }}</span>
                <span style="flex:1;color:#e5e7eb;">{{ $c->text }}</span>
                <form method="POST" action="{{ route('gameset.packs.cards.destroy', [$p, $c]) }}" style="margin:0;">@csrf @method('DELETE')<button class="gs-btn del" style="padding:.2rem .5rem;">✕</button></form>
            </div>
            @endforeach
        </div>
    </details>
    @endif
</div>
@empty
<p style="color:#9ca3af;font-size:.85rem;">No packs yet. Create one above.</p>
@endforelse
@endsection
