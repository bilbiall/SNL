@props(['size' => 'md'])
{{-- The Snakes & Liquor lockup: the neon logo + the title in Bungee with its two game icons. --}}
<a href="{{ route('home') }}" class="brand-lockup {{ $size }}" aria-label="Snakes &amp; Liquor home">
    <img class="bl-logo" src="{{ asset('img/logo-96.png') }}" alt="">
    <span class="bl-word"><x-icon name="snake" class="bl-ic a" /><b>Snakes</b><i>&amp;</i><b>Liquor</b><x-icon name="cheers" class="bl-ic b" /></span>
</a>
