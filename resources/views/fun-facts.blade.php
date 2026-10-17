@extends('layouts.site')

@section('title', 'True or false? '.max(10, $facts->count()).' fun facts to test your friends | Snakes & Liquor')
@section('meta_description', 'Can you spot the myth? A family-friendly true-or-false fun facts quiz — animals, space, food and the human body — straight from the Snakes & Liquor party game.')

@push('jsonld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org', '@type' => 'FAQPage',
    'mainEntity' => $facts->take(20)->map(fn ($f) => [
        '@type' => 'Question', 'name' => 'True or false: ' . $f->text,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => ($f->answer ? 'True. ' : 'False. ') . $f->explain],
    ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('head')
<style>
    body { background: radial-gradient(ellipse at 50% 0%, #2a1550, #0b0a16 62%); min-height: 100vh; }
    .doc { max-width: 46rem; margin: 0 auto; padding: 1rem 1.1rem 3.5rem; line-height: 1.55; }
    .doc h1 { font-family: 'Bungee', 'Figtree', sans-serif; font-weight: 400; font-size: clamp(1.5rem, 6vw, 2.3rem); line-height: 1.1; margin: 1.2rem 0 .5rem; }
    .doc p { color: #dbe3ee; } .doc a { color: #f9a8d4; }
    .ff { margin: .55rem 0; border-radius: 1rem; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); overflow: hidden; }
    .ff summary { cursor: pointer; padding: .8rem 1rem; font-weight: 800; list-style: none; display: flex; gap: .6rem; align-items: baseline; }
    .ff summary::-webkit-details-marker { display: none; } .ff summary i { font-style: normal; color: #fbbf24; font-size: .7rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; flex-shrink: 0; }
    .ff .ans { padding: 0 1rem .9rem; color: #cbd5e1; font-size: .9rem; } .ff .ans b.t { color: #6ee7b7; } .ff .ans b.f { color: #fca5a5; }
    .doc .cta { display: inline-flex; margin-top: 1.2rem; padding: .8rem 1.3rem; border-radius: 1rem; font-weight: 900; color: #fff; text-decoration: none; background: linear-gradient(135deg, #f59e0b, #ec4899); }
</style>
@endpush

@section('body')
<div class="doc">
    <x-brand />
    <h1>True or false? Fun facts to test your friends</h1>
    <p>Tap a fact to reveal the answer. These come straight from the true-or-false squares in <a href="{{ route('home') }}">Snakes &amp; Liquor</a> — the free party board game where every square has a story. Read them out at the table and see who gets caught out by the myths.</p>

    @foreach($facts as $f)
        <details class="ff"><summary><i>{{ $loop->iteration }}</i><span>{{ $f->text }}</span></summary>
            <div class="ans">@if($f->answer)<b class="t">True.</b>@else<b class="f">False.</b>@endif {{ $f->explain }}</div></details>
    @endforeach

    <a class="cta" href="{{ route('setup', ['type' => 'party']) }}">Play the full game</a>
    <x-share title="Fun facts quiz — true or false?" text="Think you can spot the myths? Try this true-or-false fun facts quiz." :url="route('fun-facts')" />
</div>
@endsection
