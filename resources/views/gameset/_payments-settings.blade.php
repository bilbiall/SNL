{{-- GameSet → Settings → Payments: pay-per-game with M-Pesa (Safaricom Daraja). $pay = GamesetPaymentController::settingsData() --}}
<div class="mb-4 mt-8" id="payments">
    <h1 style="font-weight:900;font-size:1.3rem;margin:0;">Payments (M-Pesa)</h1>
    <p style="font-size:.76rem;color:rgba(255,255,255,.45);margin:.2rem 0 0;max-width:50rem;">
        Charge for chosen modes. The host sets the game up, then pays with an M-Pesa prompt on their phone, and the room is created
        <b>only after the payment succeeds</b>. Free modes work exactly as before. Each payment buys one game, and friends join free.
        Payments are currently <b style="color:{{ $pay['enabled'] ? '#6ee7b7' : '#fca5a5' }};">{{ $pay['enabled'] ? 'ON (' . $pay['env'] . ')' : 'OFF' }}</b>.
        See every payment under the <a href="{{ route('gameset.payments.index') }}" style="color:#f9a8d4;">Payments</a> tab.
    </p>
</div>

<form method="POST" action="{{ route('gameset.settings.payments') }}" class="space-y-5">
    @csrf

    <div class="gs-box" style="background:rgba(245,158,11,.05);border:1px solid rgba(245,158,11,.18);">
        <label style="display:flex;gap:.6rem;align-items:center;font-weight:900;font-size:.9rem;"><input type="checkbox" name="enabled" value="1" {{ $pay['enabled'] ? 'checked' : '' }}> Turn payments on</label>
        <div class="space-y-2 mt-3" style="font-size:.82rem;">
            <label style="display:flex;gap:.5rem;align-items:flex-start;"><input type="radio" name="env" value="simulate" {{ $pay['env'] === 'simulate' ? 'checked' : '' }}><span><b>Simulation</b> — no real money and nothing is sent to Safaricom. Players go through the whole checkout and a pretend payment succeeds after a few seconds (a phone number ending in 0 plays someone who cancels). Use this to try everything first.</span></label>
            <label style="display:flex;gap:.5rem;align-items:flex-start;"><input type="radio" name="env" value="sandbox" {{ $pay['env'] === 'sandbox' ? 'checked' : '' }}><span><b>Sandbox</b> — Safaricom's test system, with the test keys from developer.safaricom.co.ke. No real money.</span></label>
            <label style="display:flex;gap:.5rem;align-items:flex-start;"><input type="radio" name="env" value="production" {{ $pay['env'] === 'production' ? 'checked' : '' }}><span><b>Production</b> — real money, with the keys of your approved Daraja app (Go Live).</span></label>
        </div>
    </div>

    <div class="gs-box" style="background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.2);">
        <h2 style="font-weight:900;color:#6ee7b7;font-size:.9rem;margin:0 0 .3rem;">Which modes cost money</h2>
        <p style="font-size:.72rem;color:rgba(255,255,255,.45);margin:0 0 .7rem;">Tick a mode to make it paid and set its price for one game, in KES. Unticked modes stay free.</p>
        <div class="space-y-2">
            @foreach($pay['modes'] as $k => $m)
            <div style="display:flex;align-items:center;gap:.8rem;flex-wrap:wrap;">
                <label style="display:flex;gap:.5rem;align-items:center;min-width:9rem;font-weight:800;font-size:.85rem;"><input type="checkbox" name="paid[]" value="{{ $k }}" {{ isset($pay['prices'][$k]) ? 'checked' : '' }}> {{ $m['label'] }}</label>
                <span style="font-size:.78rem;color:#9ca3af;">KES</span>
                <input type="number" name="price[{{ $k }}]" min="1" max="100000" value="{{ $pay['prices'][$k] ?? '' }}" placeholder="e.g. 50" class="gs-in" style="width:7rem;">
                <span style="font-size:.7rem;color:#6b7280;">{{ $m['adult'] ? '18+ mode' : 'for everyone' }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="gs-box" style="background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.22);">
        <h2 style="font-weight:900;color:#c4b5fd;font-size:.9rem;margin:0 0 .3rem;">Safaricom Daraja keys</h2>
        <ol style="font-size:.72rem;color:rgba(255,255,255,.5);margin:0 0 .8rem 1.1rem;list-style:decimal;line-height:1.6;">
            <li>Create an app at <b>developer.safaricom.co.ke</b> and enable <b>M-Pesa Express (Lipa na M-Pesa Online)</b>.</li>
            <li>Copy the <b>Consumer Key</b> and <b>Consumer Secret</b> of that app. For real money, also complete <b>Go Live</b> to get your Paybill or Till shortcode and its <b>Passkey</b>.</li>
            <li>Paste them below, press <b>Save</b>, then <b>Test connection</b>. Give Safaricom the callback address at the bottom.</li>
        </ol>
        <div class="grid grid-cols-12 gap-2">
            <div class="col-span-12 sm:col-span-6"><label class="gs-h">Consumer key</label><input name="consumer_key" value="{{ old('consumer_key', $pay['key']) }}" class="gs-in" autocomplete="off"></div>
            <div class="col-span-12 sm:col-span-6"><label class="gs-h">Consumer secret {{ $pay['secret_set'] ? '(saved — leave blank to keep)' : '' }}</label><input type="password" name="consumer_secret" class="gs-in" autocomplete="new-password" placeholder="{{ $pay['secret_set'] ? '••••••••' : '' }}">
                @if($pay['secret_set'])<label style="font-size:.68rem;color:#9ca3af;"><input type="checkbox" name="clear_secret" value="1"> remove the saved secret</label>@endif</div>
            <div class="col-span-12 sm:col-span-4"><label class="gs-h">Business shortcode</label><input name="shortcode" value="{{ old('shortcode', $pay['shortcode']) }}" class="gs-in" inputmode="numeric" placeholder="Paybill or Store number"></div>
            <div class="col-span-12 sm:col-span-8"><label class="gs-h">Passkey {{ $pay['passkey_set'] ? '(saved — leave blank to keep)' : '' }}</label><input type="password" name="passkey" class="gs-in" autocomplete="new-password" placeholder="{{ $pay['passkey_set'] ? '••••••••' : '' }}">
                @if($pay['passkey_set'])<label style="font-size:.68rem;color:#9ca3af;"><input type="checkbox" name="clear_passkey" value="1"> remove the saved passkey</label>@endif</div>
            <div class="col-span-12 sm:col-span-6">
                <label class="gs-h">Money goes to a</label>
                <label style="display:block;font-size:.8rem;"><input type="radio" name="txn_type" value="CustomerPayBillOnline" {{ $pay['type'] === 'CustomerPayBillOnline' ? 'checked' : '' }}> Paybill number</label>
                <label style="display:block;font-size:.8rem;"><input type="radio" name="txn_type" value="CustomerBuyGoodsOnline" {{ $pay['type'] === 'CustomerBuyGoodsOnline' ? 'checked' : '' }}> Till number (Buy Goods)</label>
            </div>
            <div class="col-span-12 sm:col-span-6"><label class="gs-h">Till number (only for Buy Goods)</label><input name="till" value="{{ old('till', $pay['till']) }}" class="gs-in" inputmode="numeric"></div>
        </div>
    </div>

    <div class="gs-box" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.1);">
        <h2 style="font-weight:900;font-size:.9rem;margin:0 0 .3rem;">Callback address</h2>
        <p style="font-size:.72rem;color:rgba(255,255,255,.45);margin:0 0 .5rem;">Safaricom reports each payment to this address. It must be reachable from the internet over https and the secret part must stay private. Put it in your Daraja app as the callback; the game sends it with every request too.</p>
        <input readonly class="gs-in" value="{{ $pay['callback'] }}" onclick="this.select();" style="font-family:monospace;font-size:.72rem;">
        <label style="font-size:.7rem;color:#9ca3af;display:block;margin-top:.4rem;"><input type="checkbox" name="new_secret" value="1"> make a new secret (the old address stops working)</label>
    </div>

    <button class="gs-btn go">Save payment settings</button>
</form>

<form method="POST" action="{{ route('gameset.settings.payments.test') }}" class="mt-3">
    @csrf <button class="gs-btn soft">Test connection with Safaricom</button>
    <span style="font-size:.7rem;color:#6b7280;margin-left:.5rem;">Save first, then test. This only asks Safaricom for an access token; it never charges anyone.</span>
</form>
