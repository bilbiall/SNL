@extends('layouts.site')

@section('robots', 'noindex,nofollow')
@section('title', 'Pay to start your game — Snakes & Liquor')

@section('body')
<div class="snl-narrow">
    <header class="flex items-center justify-between py-3 mb-2">
        <x-brand />
        <a href="{{ $back }}" class="btn btn-ghost btn-sm" id="backLink">← Change setup</a>
    </header>

    <h1 class="snl-h text-2xl mb-1"><x-icon :name="$mode['icon']" /> {{ $mode['label'] }} mode</h1>
    <p class="snl-muted text-sm mb-4">Your game is all set up. Pay with M-Pesa and it starts straight away.</p>

    @if($simulate)
        <div class="snl-alert" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);color:#fde68a;margin-bottom:1rem;">
            <b>Test mode — no real money.</b> A pretend payment succeeds a few seconds after you press Pay. (A phone number ending in 0 pretends the payment was cancelled.)
        </div>
    @endif

    <div class="snl-card mb-4">
        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:1rem;">
            <div>
                <div style="font-weight:900;">One {{ $mode['label'] }} game</div>
                <div class="snl-muted text-xs">{{ ucfirst($who) }} · everyone you invite joins free</div>
            </div>
            <div style="font-weight:900;font-size:1.6rem;white-space:nowrap;">KES {{ number_format($order->amount) }}</div>
        </div>
    </div>

    {{-- 1. the phone number --}}
    <div class="snl-card" id="stepPhone">
        <label class="snl-label" for="phone">M-Pesa phone number</label>
        <input class="snl-input" id="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="0712 345 678" value="{{ $phoneHint }}" maxlength="20">
        <p class="snl-muted text-xs mt-2">A prompt appears on this phone. Enter your M-Pesa PIN to approve KES {{ number_format($order->amount) }}.</p>
        <div id="err" class="snl-alert err" style="display:none;margin-top:.7rem;"></div>
        <button type="button" class="btn btn-primary w-full mt-3" id="payBtn" style="width:100%;justify-content:center;">Pay KES {{ number_format($order->amount) }} with M-Pesa</button>
    </div>

    {{-- 2. waiting for the phone --}}
    <div class="snl-card" id="stepWait" style="display:none;text-align:center;">
        <div style="font-size:2rem;">📱</div>
        <div style="font-weight:900;margin:.3rem 0;" id="waitTitle">Check your phone</div>
        <p class="snl-muted text-sm" id="waitText">Enter your M-Pesa PIN to approve the payment.</p>
        <div id="spin" style="margin:.8rem auto 0;width:2rem;height:2rem;border-radius:50%;border:3px solid rgba(255,255,255,.2);border-top-color:#fbbf24;animation:spin 1s linear infinite;"></div>
        <div style="display:flex;gap:.6rem;justify-content:center;margin-top:1rem;flex-wrap:wrap;">
            <button type="button" class="btn btn-ghost btn-sm" id="againBtn" style="display:none;">Send the prompt again</button>
            <button type="button" class="btn btn-ghost btn-sm" id="editBtn" style="display:none;">Use a different number</button>
        </div>
    </div>
    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

    <p class="snl-muted text-xs mt-4" style="text-align:center;">Secure payment by Safaricom M-Pesa. If a payment goes through but the game does not start, keep your M-Pesa confirmation message and contact us.</p>
</div>

<script>
(function () {
    const urls = { pay: @json(route('checkout.pay', $order)), status: @json(route('checkout.status', $order)), finish: @json(route('checkout.finish', $order)) };
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const $ = (id) => document.getElementById(id);
    const state0 = @json($state);
    let timer = null, finishing = false, sentAt = 0;

    const show = (step) => { $('stepPhone').style.display = step === 'phone' ? '' : 'none'; $('stepWait').style.display = step === 'wait' ? '' : 'none'; };
    const err = (m) => { const e = $('err'); e.textContent = m || ''; e.style.display = m ? '' : 'none'; };
    const call = (url, method, body) => fetch(url, { method, headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: body ? JSON.stringify(body) : undefined })
        .then((r) => r.json().catch(() => ({})).then((d) => ({ ok: r.ok, status: r.status, d })));

    function waiting(msg) {
        show('wait'); $('spin').style.display = ''; $('waitTitle').textContent = 'Check your phone'; $('waitText').textContent = msg || 'Enter your M-Pesa PIN to approve the payment.';
        $('againBtn').style.display = 'none'; $('editBtn').style.display = 'none';
        clearInterval(timer); sentAt = sentAt || Date.now();
        timer = setInterval(poll, 2200);
        setTimeout(() => { if (timer) { $('againBtn').style.display = ''; $('editBtn').style.display = ''; } }, 30000); // nothing after 30 s: offer a resend
    }
    function stop() { clearInterval(timer); timer = null; sentAt = 0; }

    async function poll() {
        const { d } = await call(urls.status, 'GET');
        handle(d);
    }
    async function handle(d) {
        if (!d || !d.status) return;
        if (d.status === 'paid' || d.status === 'completed') {
            stop(); show('wait'); $('spin').style.display = ''; $('waitTitle').textContent = 'Payment received ✓'; $('waitText').textContent = 'Creating your game…';
            if (finishing) return; finishing = true;
            const r = await call(urls.finish, 'POST', {});
            if (r.ok && r.d.redirect) { try { localStorage.setItem('snl_phone', $('phone').value); } catch (e) {} location.href = r.d.redirect; return; }
            finishing = false; $('spin').style.display = 'none'; $('waitTitle').textContent = 'Almost there'; $('waitText').textContent = (r.d && r.d.error) || 'The game could not be created yet. Tap to try again.';
            $('againBtn').textContent = 'Try again'; $('againBtn').style.display = ''; $('againBtn').onclick = () => handle(d);
        } else if (d.status === 'failed' || d.status === 'cancelled') {
            stop(); show('phone'); err(d.message || 'The payment did not go through.');
        } else if (d.status === 'expired') {
            stop(); show('phone'); err(d.message); $('payBtn').disabled = true;
        }
    }
    async function pay() {
        err(''); const btn = $('payBtn'); btn.disabled = true;
        const r = await call(urls.pay, 'POST', { phone: $('phone').value });
        btn.disabled = false;
        if (!r.ok && r.status !== 429) { err((r.d && r.d.error) || 'Something went wrong. Please try again.'); return; }
        if (r.status === 429 && r.d && r.d.status === 'pending') { waiting('A prompt was just sent. Check your phone.'); return; }
        sentAt = Date.now(); waiting(r.d.message || '');
    }

    $('payBtn').onclick = pay;
    $('againBtn').onclick = () => { stop(); show('phone'); pay(); };
    $('editBtn').onclick = () => { stop(); show('phone'); };
    $('phone').addEventListener('keydown', (e) => { if (e.key === 'Enter') pay(); });
    try { if (!$('phone').value) $('phone').value = localStorage.getItem('snl_phone') || ''; } catch (e) {}

    // coming back to this page (refresh, or a callback that arrived while the page was closed)
    if (state0.status === 'pending') waiting(state0.message);
    else if (state0.status === 'paid' || state0.status === 'completed') handle(state0);
    else if (state0.status === 'expired') { err(state0.message); $('payBtn').disabled = true; }
    else if (state0.status === 'failed' || state0.status === 'cancelled') err(state0.message);
})();
</script>
@endsection
