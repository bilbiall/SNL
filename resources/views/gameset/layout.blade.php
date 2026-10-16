@extends('layouts.site')

@push('head')
<style>
    .gs-nav { position: sticky; top: 0; z-index: 50; background: rgba(8,7,16,.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--line); }
    .gs-tab { padding: .45rem .9rem; border-radius: .7rem; font-weight: 800; font-size: .82rem; color: #9ca3af; text-decoration: none; }
    .gs-tab.on { background: rgba(245,158,11,.16); color: #fbbf24; }
    .gs-in { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); border-radius: .55rem; padding: .4rem .55rem; font-size: .78rem; color: #fff; font-family: inherit; outline: none; width: 100%; box-sizing: border-box; }
    .gs-in:focus { border-color: rgba(245,158,11,.6); }
    select.gs-in option { background: #1a1a2e; }
    .gs-h { font-size: .66rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: #6b7280; }
    .gs-btn { font-size: .72rem; font-weight: 800; padding: .45rem .8rem; border-radius: .6rem; cursor: pointer; border: none; color: #fff; white-space: nowrap; font-family: inherit; }
    .gs-btn.go { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .gs-btn.ok { background: linear-gradient(135deg, #10b981, #059669); }
    .gs-btn.vio { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .gs-btn.soft { background: rgba(99,102,241,.15); border: 1px solid rgba(99,102,241,.3); color: #a5b4fc; }
    .gs-btn.del { background: rgba(248,113,113,.1); border: 1px solid rgba(248,113,113,.25); color: #fca5a5; }
    .gs-box { border-radius: 1rem; padding: 1.1rem; margin-bottom: 1.5rem; }
</style>
@endpush

@section('body')
<div class="gs-nav">
    <div style="max-width:1150px;margin:0 auto;padding:.7rem 1rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
        <a href="{{ route('home') }}" class="logo" style="margin-right:.6rem;font-size:1rem;color:#fff;text-decoration:none;">S<b>&amp;</b>L <span style="font-size:.7rem;color:#9ca3af;">GameSet</span></a>
        <a href="{{ route('gameset.boards.index') }}" class="gs-tab {{ request()->routeIs('gameset.boards.*') ? 'on' : '' }}">Boards</a>
        <a href="{{ route('gameset.cards.index') }}" class="gs-tab {{ request()->routeIs('gameset.cards.*') ? 'on' : '' }}">Cards</a>
        <a href="{{ route('gameset.words.index') }}" class="gs-tab {{ request()->routeIs('gameset.words.*') ? 'on' : '' }}">Words</a>
        <span style="flex:1;"></span>
        <a href="{{ route('home') }}" class="gs-tab" target="_blank">↗ View game</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">@csrf<button class="gs-tab" style="background:none;border:none;cursor:pointer;font-family:inherit;">Sign out</button></form>
    </div>
</div>
<div style="max-width:1150px;margin:0 auto;padding:1.4rem 1rem 3rem;">
    @if(session('success'))<div class="snl-alert ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="snl-alert err">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
    @yield('gameset')
</div>
@endsection
