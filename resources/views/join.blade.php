@extends('layouts.site')

@section('title', 'Join a room — Snakes & Liquor')

@section('body')
<div class="snl-narrow">
    <header class="flex items-center justify-between py-3 mb-2">
        <a href="{{ route('home') }}" class="logo text-lg"><x-icon name="snake" /> Snakes <b>&amp;</b> Liquor <x-icon name="cheers" /></a>
    </header>

    <h1 class="snl-h text-2xl mb-1"><x-icon name="globe" /> Join a room</h1>
    <p class="snl-muted text-sm mb-5">Ask the host for the 5-letter code, then pop your name in.</p>

    @if($errors->any())
        <div class="snl-alert err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <form method="POST" action="{{ route('join.store') }}" class="snl-card space-y-4">
        @csrf
        <div>
            <label class="snl-label" for="code">Room code</label>
            <input class="snl-input" id="code" name="code" value="{{ old('code', $code) }}" maxlength="8" required autocomplete="off"
                   style="text-transform:uppercase;letter-spacing:.3em;text-align:center;font-size:1.5rem;font-weight:900;">
        </div>
        <div>
            <label class="snl-label" for="name">Your name &amp; avatar</label>
            <div class="flex items-center gap-3">
                <button type="button" class="avbtn" id="avatarBtn" title="Choose your avatar"><img id="avatarImg" alt=""></button>
                <input type="hidden" name="avatar" id="avatar" value="{{ old('avatar', $avatarDefault) }}">
                <input class="snl-input" id="name" name="name" value="{{ old('name') }}" maxlength="24" required autocomplete="off" placeholder="e.g. Brian">
            </div>
        </div>
        <label class="flex items-start gap-2 text-sm font-bold">
            <input type="checkbox" name="adult" value="1" {{ old('adult') ? 'checked' : '' }} class="mt-1" style="width:1.1rem;height:1.1rem;">
            <span>I'm <b>18 or over</b> <span class="snl-muted font-semibold">(needed for Drinks rooms — Family rooms don't mind).</span></span>
        </label>
        <button class="btn btn-violet btn-block" style="padding:1rem;">Join the table</button>
    </form>

    <footer class="snl-foot">Drink responsibly. Never drive after drinking.</footer>
</div>

@include('partials.avatar-picker')
<script>
(function () {
    var av = document.getElementById('avatar'), img = document.getElementById('avatarImg');
    function show() { img.src = SNLAvatars.url(av.value); }
    document.getElementById('avatarBtn').onclick = function () { SNLAvatars.open(av.value, function (k) { av.value = k; show(); }); };
    show();
})();
</script>
@endsection
