@extends('gameset.layout')

@section('title', 'Payments — GameSet')

@php
    $kes = fn ($n) => 'KES ' . number_format($n);
    $badge = fn ($s) => ['completed' => ['Game created', '#6ee7b7'], 'paid' => ['Paid', '#6ee7b7'], 'pending' => ['Waiting', '#fcd34d'], 'created' => ['Not paid yet', '#9ca3af'],
        'failed' => ['Failed', '#fca5a5'], 'cancelled' => ['Cancelled', '#fdba74'], 'expired' => ['Expired', '#6b7280']][$s] ?? [$s, '#9ca3af'];
    $max = max(1, $days->max());
@endphp

@section('gameset')
<div class="mb-4" style="display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 style="font-weight:900;font-size:1.3rem;margin:0;">Payments</h1>
        <p style="font-size:.76rem;color:rgba(255,255,255,.45);margin:.2rem 0 0;">
            M-Pesa money for paid modes.
            Payments are <b style="color:{{ $enabled ? '#6ee7b7' : '#fca5a5' }};">{{ $enabled ? 'ON (' . $liveEnv . ')' : 'OFF' }}</b>
            @if($enabled && $prices) · paid modes: {{ collect($prices)->map(fn ($p, $m) => ($modes[$m]['label'] ?? $m) . ' ' . $kes($p))->implode(', ') }} @endif
            · <a href="{{ route('gameset.settings') }}#payments" style="color:#f9a8d4;">Change settings</a>
        </p>
    </div>
    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
        @foreach(['production' => 'Real money', 'sandbox' => 'Sandbox', 'simulate' => 'Simulation'] as $e => $label)
            <a href="{{ route('gameset.payments.index', ['env' => $e]) }}" class="gs-tab {{ $env === $e ? 'on' : '' }}" style="border:1px solid rgba(255,255,255,.1);">{{ $label }}</a>
        @endforeach
        <a href="{{ route('gameset.payments.export', ['env' => $env]) }}" class="gs-btn soft" style="text-decoration:none;display:inline-block;">Download CSV</a>
    </div>
</div>

@if($env !== 'production')<div class="snl-alert" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);color:#fde68a;margin-bottom:1rem;">You are looking at {{ $env === 'simulate' ? 'SIMULATED' : 'SANDBOX' }} payments — pretend money, kept apart from real income.</div>@endif
@if($stuck)<div class="snl-alert err" style="margin-bottom:1rem;">{{ $stuck }} payment{{ $stuck === 1 ? '' : 's' }} came in but the game was never created (the customer closed the page). Filter by "Paid" below; they can still tap their checkout link, or you may refund them.</div>@endif

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    @foreach(['today' => 'Today', 'week' => 'Last 7 days', 'month' => 'Last 30 days', 'all' => 'All time'] as $k => $label)
    <div class="gs-box" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);margin:0;padding:.8rem 1rem;">
        <div class="gs-h">{{ $label }}</div>
        <div style="font-weight:900;font-size:1.35rem;margin-top:.15rem;">{{ $kes($tiles[$k]) }}</div>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-3 mb-6">
    <div class="gs-box lg:col-span-2" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.1);margin:0;">
        <div class="gs-h" style="margin-bottom:.5rem;">Money per day (last 14 days)</div>
        <div style="display:flex;align-items:flex-end;gap:.35rem;height:7.5rem;" role="img" aria-label="Revenue per day for the last 14 days">
            @foreach($days as $d => $amt)
                <div title="{{ \Illuminate\Support\Carbon::parse($d)->format('D j M') }}: {{ $kes($amt) }}" style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;height:100%;">
                    <span style="font-size:.55rem;color:#9ca3af;margin-bottom:.15rem;">{{ $amt ? number_format($amt) : '' }}</span>
                    <div style="width:100%;border-radius:.3rem .3rem 0 0;background:linear-gradient(180deg,#fbbf24,#ec4899);height:{{ $amt ? max(4, round($amt / $max * 80)) : 2 }}%;opacity:{{ $amt ? 1 : .25 }};"></div>
                    <span style="font-size:.55rem;color:#6b7280;margin-top:.2rem;">{{ \Illuminate\Support\Carbon::parse($d)->format('j') }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="gs-box" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.1);margin:0;">
        <div class="gs-h" style="margin-bottom:.4rem;">Last 30 days</div>
        <div style="font-size:.85rem;line-height:1.7;">
            Checkouts started: <b>{{ $started }}</b><br>
            Paid: <b>{{ $paid30 }}</b>{{ $conversion !== null ? ' (' . $conversion . '%)' : '' }}<br>
            Waiting for the phone now: <b>{{ $open }}</b>
        </div>
        <div class="gs-h" style="margin:.7rem 0 .3rem;">Money by mode (all time)</div>
        @forelse($byMode as $r)
            <div style="display:flex;justify-content:space-between;font-size:.82rem;"><span>{{ $modes[$r->mode]['label'] ?? $r->mode }} <span style="color:#6b7280;">· {{ $r->n }} games</span></span><b>{{ $kes($r->total) }}</b></div>
        @empty
            <div style="font-size:.78rem;color:#6b7280;">No paid games yet.</div>
        @endforelse
    </div>
</div>

<form method="GET" action="{{ route('gameset.payments.index') }}" class="gs-box" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.1);padding:.8rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1rem;">
    <input type="hidden" name="env" value="{{ $env }}">
    <div><label class="gs-h">Status</label><select name="status" class="gs-in" style="width:auto;"><option value="">All</option><option value="money" {{ $status === 'money' ? 'selected' : '' }}>Money in (paid)</option>
        @foreach(['pending' => 'Waiting', 'paid' => 'Paid, no game yet', 'completed' => 'Game created', 'failed' => 'Failed', 'cancelled' => 'Cancelled', 'created' => 'Not paid yet', 'expired' => 'Expired'] as $k => $l)<option value="{{ $k }}" {{ $status === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
    <div><label class="gs-h">Mode</label><select name="mode" class="gs-in" style="width:auto;"><option value="">All</option>@foreach($modes as $k => $m)<option value="{{ $k }}" {{ $mode === $k ? 'selected' : '' }}>{{ $m['label'] }}</option>@endforeach</select></div>
    <div><label class="gs-h">From</label><input type="date" name="from" value="{{ $from }}" class="gs-in" style="width:auto;"></div>
    <div><label class="gs-h">To</label><input type="date" name="to" value="{{ $to }}" class="gs-in" style="width:auto;"></div>
    <div><label class="gs-h">M-Pesa code or phone</label><input name="q" value="{{ $q }}" class="gs-in" style="width:11rem;" placeholder="e.g. NLJ7RT61SV"></div>
    <button class="gs-btn go">Filter</button>
    <a href="{{ route('gameset.payments.index', ['env' => $env]) }}" class="gs-btn soft" style="text-decoration:none;">Clear</a>
</form>

<div class="gs-box" style="padding:0;overflow-x:auto;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.08);">
    <table style="width:100%;border-collapse:collapse;font-size:.78rem;min-width:46rem;">
        <thead><tr style="text-align:left;color:#9ca3af;font-size:.66rem;letter-spacing:.07em;text-transform:uppercase;">
            <th style="padding:.6rem .8rem;">When</th><th>Mode</th><th>Amount</th><th>Phone</th><th>Status</th><th>M-Pesa code</th><th>Game</th><th></th></tr></thead>
        <tbody>
        @forelse($list as $p)
            @php [$bl, $bc] = $badge($p->status); @endphp
            <tr style="border-top:1px solid rgba(255,255,255,.07);">
                <td style="padding:.55rem .8rem;white-space:nowrap;">{{ ($p->paid_at ?? $p->created_at)->format('j M, H:i') }}</td>
                <td>{{ $modes[$p->mode]['label'] ?? $p->mode }}</td>
                <td style="font-weight:800;">{{ $kes($p->amount) }}</td>
                <td style="color:#9ca3af;">{{ $p->maskedPhone() }}</td>
                <td><span style="font-size:.66rem;font-weight:900;padding:.12rem .5rem;border-radius:999px;border:1px solid {{ $bc }};color:{{ $bc }};white-space:nowrap;">{{ $bl }}</span>
                    @if(in_array($p->status, ['failed','cancelled']) && $p->result_desc)<div style="font-size:.64rem;color:#6b7280;max-width:14rem;">{{ $p->result_desc }}</div>@endif</td>
                <td style="font-family:monospace;">{{ $p->receipt ?? '—' }}</td>
                <td>@if($p->room_id && isset($rooms[$p->room_id]))<a href="{{ route('tv.show', $rooms[$p->room_id]) }}" target="_blank" style="color:#f9a8d4;">{{ $rooms[$p->room_id] }}</a>@elseif($p->status === 'paid')<span style="color:#fca5a5;">not created</span>@else — @endif</td>
                <td>@if($p->status === 'pending' && $p->env !== 'simulate')<form method="POST" action="{{ route('gameset.payments.check', $p) }}" style="margin:0;">@csrf<button class="gs-btn soft" style="padding:.2rem .5rem;">Check with M-Pesa</button></form>@endif</td>
            </tr>
        @empty
            <tr><td colspan="8" style="padding:1.2rem;color:#6b7280;">No payments match. Once a paid mode is played, every attempt shows up here.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.8rem;">{{ $list->links() }}</div>
@endsection
