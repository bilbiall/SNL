@extends('layouts.site')

@section('title', 'GameSet sign-in — Snakes & Liquor')

@section('body')
<div class="snl-narrow" style="padding-top:4rem;">
    <a href="{{ route('home') }}" class="logo text-lg block mb-6"><x-icon name="snake" /> Snakes <b>&amp;</b> Liquor <x-icon name="cheers" /></a>
    <h1 class="snl-h text-2xl mb-1">GameSet</h1>
    <p class="snl-muted text-sm mb-5">Admin sign-in — boards, squares and cards.</p>

    <form method="POST" action="{{ route('login.attempt') }}" class="snl-card space-y-4">
        @csrf
        @error('email')<div class="snl-alert err" style="margin:0;">{{ $message }}</div>@enderror
        <div>
            <label class="snl-label" for="email">Email</label>
            <input class="snl-input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>
        <div>
            <label class="snl-label" for="password">Password</label>
            <input class="snl-input" id="password" type="password" name="password" required autocomplete="current-password">
        </div>
        <label class="flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
        <button class="btn btn-primary btn-block">Sign in</button>
    </form>
</div>
@endsection
