<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         | Rate limits are keyed per SESSION (i.e. per phone), never per IP for gameplay: a bar or
         | house party puts a dozen players behind one Wi-Fi address, and they must not share an
         | allowance. Each limiter is also separate, so polling can never eat the login quota.
         */
        $device = fn (Request $r) => $r->hasSession() ? $r->session()->getId() : $r->ip();

        RateLimiter::for('snl-play', fn (Request $r) => Limit::perMinute(240)->by('play|' . $device($r)));
        RateLimiter::for('snl-voice', fn (Request $r) => Limit::perMinute(420)->by('voice|' . $device($r))); // a phone in voice polls about once a second
        RateLimiter::for('snl-state', fn (Request $r) => Limit::perMinute(150)->by('state|' . $device($r)));
        // Creating/joining rooms: per phone, but with an IP backstop generous enough for a busy venue.
        RateLimiter::for('snl-create', fn (Request $r) => [
            Limit::perMinute(30)->by('create|' . $device($r)),
            Limit::perMinute(300)->by('create-ip|' . $r->ip()),
        ]);
        RateLimiter::for('snl-login', fn (Request $r) => Limit::perMinute(8)->by('login|' . $r->ip() . '|' . strtolower((string) $r->input('email'))));
    }
}
