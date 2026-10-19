@extends('gameset.layout')

@section('title', 'Settings — GameSet')

@section('gameset')
<div class="mb-4">
    <h1 style="font-weight:900;font-size:1.3rem;margin:0;">Settings</h1>
    <p style="font-size:.76rem;color:rgba(255,255,255,.45);margin:.2rem 0 0;max-width:48rem;">
        Voice chat lets people in an <b>online</b> room talk to each other from their phones. Everyone starts muted and taps the microphone to speak.
        The TV screen and party mode (one phone) never have voice. Right now voice chat is <b style="color:{{ $effective === 'off' ? '#fca5a5' : '#6ee7b7' }};">{{ strtoupper($effective) }}</b>.
    </p>
</div>

<form method="POST" action="{{ route('gameset.settings.save') }}" class="space-y-5">
    @csrf

    <div class="gs-box" style="background:rgba(245,158,11,.05);border:1px solid rgba(245,158,11,.18);">
        <h2 style="font-weight:900;color:#fbbf24;font-size:.9rem;margin:0 0 .6rem;">Voice chat</h2>
        <div class="space-y-2" style="font-size:.82rem;">
            <label style="display:flex;gap:.5rem;align-items:flex-start;"><input type="radio" name="mode" value="off" {{ $s['mode'] === 'off' || !in_array($s['mode'], ['p2p','livekit'], true) ? 'checked' : '' }}><span><b>Off</b> — no microphone button.</span></label>
            <label style="display:flex;gap:.5rem;align-items:flex-start;"><input type="radio" name="mode" value="p2p" {{ $s['mode'] === 'p2p' ? 'checked' : '' }}><span><b>Free: phone to phone</b> — costs nothing. Phones connect to each other directly and your server only passes small "hello" messages. Best for rooms of up to about 6 talkers. Some mobile networks block direct connections; add a relay (TURN) below if people cannot hear each other.</span></label>
            <label style="display:flex;gap:.5rem;align-items:flex-start;"><input type="radio" name="mode" value="livekit" {{ $s['mode'] === 'livekit' ? 'checked' : '' }}><span><b>LiveKit</b> — a hosted voice service (free tier, then paid). More reliable and handles bigger rooms. Needs the three LiveKit boxes below.</span></label>
        </div>
    </div>

    <div class="gs-box" style="background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.2);">
        <h2 style="font-weight:900;color:#6ee7b7;font-size:.9rem;margin:0 0 .3rem;">Free option: phone to phone</h2>
        <p style="font-size:.72rem;color:rgba(255,255,255,.45);margin:0 0 .7rem;">The defaults work for many players. The relay is only needed for people whose network blocks direct connections.</p>
        <div class="grid grid-cols-12 gap-2">
            <div class="col-span-12 sm:col-span-3"><label class="gs-h">Most people talking at once</label><input type="number" name="max_p2p" value="{{ old('max_p2p', $s['max_p2p']) }}" min="2" max="12" class="gs-in"></div>
            <div class="col-span-12 sm:col-span-9"><label class="gs-h">Finder servers (STUN), one per line</label><textarea name="stun" rows="2" class="gs-in">{{ old('stun', $s['stun']) }}</textarea></div>
            <div class="col-span-12"><label class="gs-h">Relay servers (TURN), one per line — optional, e.g. turn:relay.example.com:3478 and turns:relay.example.com:443?transport=tcp</label><textarea name="turn_urls" rows="2" class="gs-in" placeholder="turn:…">{{ old('turn_urls', $s['turn_urls']) }}</textarea></div>
            <div class="col-span-12 sm:col-span-6"><label class="gs-h">Relay username</label><input name="turn_user" value="{{ old('turn_user', $s['turn_user']) }}" class="gs-in" autocomplete="off"></div>
            <div class="col-span-12 sm:col-span-6"><label class="gs-h">Relay password {{ $s['turn_pass_set'] ? '(saved — leave blank to keep)' : '' }}</label><input type="password" name="turn_pass" class="gs-in" autocomplete="new-password" placeholder="{{ $s['turn_pass_set'] ? '••••••••' : '' }}">
                @if($s['turn_pass_set'])<label style="font-size:.68rem;color:#9ca3af;"><input type="checkbox" name="clear_turn_pass" value="1"> remove the saved password</label>@endif</div>
        </div>
    </div>

    <div class="gs-box" style="background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.22);">
        <h2 style="font-weight:900;color:#c4b5fd;font-size:.9rem;margin:0 0 .3rem;">LiveKit</h2>
        <ol style="font-size:.72rem;color:rgba(255,255,255,.5);margin:0 0 .8rem 1.1rem;list-style:decimal;line-height:1.6;">
            <li>Create a free account at <b>cloud.livekit.io</b> and make a project.</li>
            <li>Open the project's <b>Settings → Keys</b> and create a key.</li>
            <li>Copy the <b>URL</b> (starts with wss://), the <b>API key</b> and the <b>API secret</b> into the boxes below, choose LiveKit above and save.</li>
        </ol>
        <div class="grid grid-cols-12 gap-2">
            <div class="col-span-12"><label class="gs-h">Server URL</label><input name="lk_url" value="{{ old('lk_url', $s['lk_url']) }}" class="gs-in" placeholder="wss://your-project.livekit.cloud" autocomplete="off"></div>
            <div class="col-span-12 sm:col-span-6"><label class="gs-h">API key</label><input name="lk_key" value="{{ old('lk_key', $s['lk_key']) }}" class="gs-in" autocomplete="off"></div>
            <div class="col-span-12 sm:col-span-6"><label class="gs-h">API secret {{ $s['lk_secret_set'] ? '(saved — leave blank to keep)' : '' }}</label><input type="password" name="lk_secret" class="gs-in" autocomplete="new-password" placeholder="{{ $s['lk_secret_set'] ? '••••••••' : '' }}">
                @if($s['lk_secret_set'])<label style="font-size:.68rem;color:#9ca3af;"><input type="checkbox" name="clear_lk_secret" value="1"> remove the saved secret</label>@endif</div>
            <div class="col-span-12"><label class="gs-h">LiveKit browser library (advanced — only change if voice fails to start)</label><input name="lk_sdk" value="{{ old('lk_sdk', $s['lk_sdk']) }}" class="gs-in" autocomplete="off"><p style="font-size:.66rem;color:#6b7280;margin:.2rem 0 0;">Must be an https link to a .js file on cdn.jsdelivr.net, unpkg.com or cdnjs.cloudflare.com. Default: {{ $defaultSdk }}</p></div>
        </div>
    </div>

    <button class="gs-btn go">Save settings</button>
</form>
@endsection
