<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Voice;
use Illuminate\Http\Request;

/** GameSet → Settings: the voice chat provider and its keys. Secrets are encrypted and never shown again once saved. */
class GamesetSettingsController extends Controller
{
    public function index()
    {
        return view('gameset.settings', [
            's' => [
                'mode' => (string) Setting::get('voice.mode', 'off'),
                'max_p2p' => Voice::maxP2p(),
                'stun' => (string) Setting::get('voice.stun', Voice::DEFAULT_STUN),
                'turn_urls' => (string) Setting::get('voice.turn_urls', ''),
                'turn_user' => (string) Setting::get('voice.turn_user', ''),
                'turn_pass_set' => Setting::has('voice.turn_pass'),
                'lk_url' => (string) Setting::get('voice.lk_url', ''),
                'lk_key' => (string) Setting::get('voice.lk_key', ''),
                'lk_secret_set' => Setting::has('voice.lk_secret'),
                'lk_sdk' => (string) Setting::get('voice.lk_sdk', Voice::DEFAULT_SDK),
            ],
            'effective' => Voice::mode(),
            'defaultSdk' => Voice::DEFAULT_SDK,
        ]);
    }

    public function save(Request $request)
    {
        $d = $request->validate([
            'mode'      => 'required|in:off,p2p,livekit',
            'max_p2p'   => 'nullable|integer|min:2|max:12',
            'stun'      => 'nullable|string|max:600',
            'turn_urls' => 'nullable|string|max:600',
            'turn_user' => 'nullable|string|max:120',
            'turn_pass' => 'nullable|string|max:200',
            'clear_turn_pass' => 'nullable|boolean',
            'lk_url'    => ['nullable', 'string', 'max:200', 'regex:#^(wss?|https?)://[^\s]+$#i'],
            'lk_key'    => 'nullable|string|max:120',
            'lk_secret' => 'nullable|string|max:200',
            'clear_lk_secret' => 'nullable|boolean',
            'lk_sdk'    => ['nullable', 'string', 'max:300', 'regex:#^https://(cdn\.jsdelivr\.net|unpkg\.com|cdnjs\.cloudflare\.com)/[^\s]+\.js$#i'],
        ]);

        Setting::put('voice.mode', $d['mode']);
        Setting::put('voice.max_p2p', (string) ($d['max_p2p'] ?? 6));
        Setting::put('voice.stun', $this->clean($d['stun'] ?? null));
        Setting::put('voice.turn_urls', $this->clean($d['turn_urls'] ?? null));
        Setting::put('voice.turn_user', $this->clean($d['turn_user'] ?? null));
        Setting::put('voice.lk_url', $this->clean($d['lk_url'] ?? null));
        Setting::put('voice.lk_key', $this->clean($d['lk_key'] ?? null));
        Setting::put('voice.lk_sdk', $this->clean($d['lk_sdk'] ?? null));
        // a secret box left blank keeps what is saved; the "remove" tick clears it
        if ($request->boolean('clear_turn_pass')) {
            Setting::putSecret('voice.turn_pass', null);
        } elseif (filled($d['turn_pass'] ?? null)) {
            Setting::putSecret('voice.turn_pass', $d['turn_pass']);
        }
        if ($request->boolean('clear_lk_secret')) {
            Setting::putSecret('voice.lk_secret', null);
        } elseif (filled($d['lk_secret'] ?? null)) {
            Setting::putSecret('voice.lk_secret', $d['lk_secret']);
        }

        $note = $d['mode'] === 'livekit' && Voice::mode() !== 'livekit'
            ? ' LiveKit is not on yet: it needs the server URL, API key and API secret.' : '';

        return redirect()->route('gameset.settings')->with('success', 'Settings saved. Voice chat is now: ' . strtoupper(Voice::mode()) . '.' . $note);
    }

    private function clean(?string $v): ?string
    {
        $v = $v === null ? null : trim($v);

        return $v === '' ? null : $v;
    }
}
