<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Room;
use App\Models\Setting;
use App\Services\Daraja;
use App\Services\PaymentException;
use App\Support\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * GameSet → Payments: the M-Pesa dashboard (money in, who paid for what, stuck payments) and the Payments card on the Settings page
 * (the Daraja keys, the on/off switch, which modes cost money and how much). Secrets are encrypted and never shown again once saved.
 */
class GamesetPaymentController extends Controller
{
    // ───────────────────────────── dashboard ─────────────────────────────

    public function index(Request $request)
    {
        Payment::expireStale();
        $status = $request->query('status');
        $mode = $request->query('mode');
        $env = $request->query('env', Payments::env());
        $from = $this->date($request->query('from'));
        $to = $this->date($request->query('to'));
        $q = trim((string) $request->query('q', ''));

        $base = Payment::query()->where('env', $env);
        $list = (clone $base)
            ->when(in_array($status, ['created', 'pending', 'paid', 'completed', 'failed', 'cancelled', 'expired'], true), fn ($x) => $x->where('status', $status))
            ->when($status === 'money', fn ($x) => $x->money())
            ->when(isset(config('snl.modes')[$mode]), fn ($x) => $x->where('mode', $mode))
            ->when($from, fn ($x) => $x->where('created_at', '>=', $from->startOfDay()))
            ->when($to, fn ($x) => $x->where('created_at', '<=', $to->endOfDay()))
            ->when($q !== '', fn ($x) => $x->where(fn ($y) => $y->where('receipt', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")->orWhere('token', 'like', "{$q}%")))
            ->orderByDesc('id')->paginate(25)->withQueryString();

        $sum = fn ($since = null) => (int) (clone $base)->money()->when($since, fn ($x) => $x->where('paid_at', '>=', $since))->sum('amount');
        $started = (clone $base)->where('created_at', '>=', now()->subDays(30))->count();
        $paid30 = (clone $base)->money()->where('created_at', '>=', now()->subDays(30))->count();

        // revenue per day for the last 14 days
        $days = collect(range(13, 0))->mapWithKeys(fn ($d) => [now()->subDays($d)->toDateString() => 0]);
        (clone $base)->money()->where('paid_at', '>=', now()->subDays(13)->startOfDay())->get(['amount', 'paid_at'])
            ->each(function ($p) use (&$days) {
                $k = $p->paid_at->toDateString();
                if ($days->has($k)) {
                    $days[$k] = $days[$k] + $p->amount;
                }
            });

        $byMode = (clone $base)->money()->selectRaw('mode, count(*) as n, sum(amount) as total')->groupBy('mode')->orderByDesc('total')->get();
        $stuck = (clone $base)->where('status', 'paid')->whereNull('room_id')->where('paid_at', '<', now()->subMinutes(2))->count(); // paid, but the room was never created

        return view('gameset.payments', [
            'list' => $list, 'env' => $env, 'status' => $status, 'mode' => $mode, 'q' => $q, 'from' => $from?->toDateString(), 'to' => $to?->toDateString(),
            'tiles' => ['today' => $sum(now()->startOfDay()), 'week' => $sum(now()->subDays(6)->startOfDay()), 'month' => $sum(now()->subDays(29)->startOfDay()), 'all' => $sum()],
            'conversion' => $started ? round($paid30 / $started * 100) : null, 'started' => $started, 'paid30' => $paid30,
            'open' => (clone $base)->where('status', 'pending')->count(), 'stuck' => $stuck,
            'days' => $days, 'byMode' => $byMode, 'modes' => config('snl.modes'),
            'enabled' => Payments::enabled(), 'liveEnv' => Payments::env(), 'prices' => Payments::prices(),
            'rooms' => Room::whereIn('id', $list->pluck('room_id')->filter())->pluck('join_code', 'id'),
        ]);
    }

    /** Every payment as a spreadsheet (with the full phone numbers: for refunds and bookkeeping). */
    public function export(Request $request)
    {
        $env = $request->query('env', Payments::env());
        $rows = Payment::where('env', $env)->orderBy('id')->cursor();
        $name = 'snakes-and-liquor-payments-' . $env . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'created_at', 'paid_at', 'mode', 'amount_kes', 'phone', 'status', 'mpesa_receipt', 'result_code', 'result_desc', 'room_id', 'environment']);
            foreach ($rows as $p) {
                fputcsv($out, [$p->id, $p->created_at, $p->paid_at, $p->mode, $p->amount, $p->phone, $p->status, $p->receipt, $p->result_code, $p->result_desc, $p->room_id, $p->env]);
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Asks M-Pesa what happened to a payment that is still open (for when the callback never arrived). */
    public function check(Payment $payment, Daraja $daraja)
    {
        if ($payment->status !== 'pending') {
            return back()->with('success', 'That payment is not waiting for an answer.');
        }
        if ($payment->env === 'simulate') {
            return back()->with('success', 'Simulated payments settle by themselves a few seconds after the customer opens the checkout.');
        }
        $r = $daraja->stkQuery($payment);
        if (!$r['done']) {
            return back()->with('success', 'M-Pesa has no answer yet: the customer has not entered their PIN, or the prompt is still open.');
        }
        Payments::applyResult($payment, $r['code'], $r['desc'] ?? '');

        return back()->with('success', 'Updated from M-Pesa: ' . $payment->fresh()->status . ($r['desc'] ? ' — ' . $r['desc'] : ''));
    }

    // ───────────────────────────── settings (the card on GameSet → Settings) ─────────────────────────────

    /** The values the settings card shows. */
    public static function settingsData(): array
    {
        $d = Payments::daraja();

        return [
            'enabled' => (string) Setting::get('pay.enabled', '0') === '1', 'env' => Payments::env(),
            'key' => $d['key'], 'secret_set' => Setting::has('pay.consumer_secret'), 'shortcode' => $d['shortcode'],
            'passkey_set' => Setting::has('pay.passkey'), 'type' => $d['type'], 'till' => (string) Setting::get('pay.till', ''),
            'prices' => Payments::prices(), 'callback' => Payments::callbackUrl(), 'ready' => Payments::ready(),
            'modes' => collect(config('snl.modes'))->filter(fn ($m) => $m['enabled'])->all(),
        ];
    }

    public function saveSettings(Request $request)
    {
        $modeKeys = array_keys(config('snl.modes'));
        $d = $request->validate([
            'env' => 'required|in:simulate,sandbox,production',
            'consumer_key' => 'nullable|string|max:200', 'consumer_secret' => 'nullable|string|max:300', 'clear_secret' => 'nullable|boolean',
            'shortcode' => ['nullable', 'regex:/^\d{5,8}$/'], 'passkey' => 'nullable|string|max:300', 'clear_passkey' => 'nullable|boolean',
            'txn_type' => 'required|in:CustomerPayBillOnline,CustomerBuyGoodsOnline', 'till' => ['nullable', 'regex:/^\d{5,8}$/'],
            'paid' => 'nullable|array', 'paid.*' => 'in:' . implode(',', $modeKeys),
            'price' => 'nullable|array', 'price.*' => 'nullable|integer|min:1|max:100000',
            'new_secret' => 'nullable|boolean',
        ], ['shortcode.regex' => 'The shortcode should be 5 to 8 digits.', 'till.regex' => 'The till number should be 5 to 8 digits.']);

        Setting::put('pay.env', $d['env']);
        Setting::put('pay.consumer_key', $this->clean($d['consumer_key'] ?? null));
        Setting::put('pay.shortcode', $this->clean($d['shortcode'] ?? null));
        Setting::put('pay.txn_type', $d['txn_type']);
        Setting::put('pay.till', $this->clean($d['till'] ?? null));
        if ($request->boolean('clear_secret')) {
            Setting::putSecret('pay.consumer_secret', null);
        } elseif (filled($d['consumer_secret'] ?? null)) {
            Setting::putSecret('pay.consumer_secret', trim($d['consumer_secret']));
        }
        if ($request->boolean('clear_passkey')) {
            Setting::putSecret('pay.passkey', null);
        } elseif (filled($d['passkey'] ?? null)) {
            Setting::putSecret('pay.passkey', trim($d['passkey']));
        }
        if ($request->boolean('new_secret')) {
            Setting::put('pay.callback_secret', Str::random(40)); // the old callback address stops working: tell Safaricom the new one
        }
        $prices = [];
        foreach ((array) ($d['paid'] ?? []) as $m) {
            $prices[$m] = (int) ($d['price'][$m] ?? 0);
        }
        $missing = array_keys(array_filter($prices, fn ($p) => $p < 1));
        $prices = array_filter($prices, fn ($p) => $p >= 1);
        Setting::put('pay.prices', json_encode($prices));

        $wantsOn = $request->boolean('enabled');
        $notes = [];
        if ($wantsOn && !Payments::ready()) {
            $wantsOn = false;
            $notes[] = 'Payments were NOT switched on: ' . ($d['env'] === 'simulate' ? '' : 'the consumer key, consumer secret, shortcode and passkey are all needed. Fill them in, or choose Simulation to try the flow without real money.');
        }
        Setting::put('pay.enabled', $wantsOn ? '1' : '0');
        if ($missing) {
            $notes[] = 'No price was given for ' . implode(', ', array_map(fn ($m) => config("snl.modes.$m.label"), $missing)) . ', so it stays free.';
        }
        if ($wantsOn && !$prices) {
            $notes[] = 'Payments are on, but no mode is marked as paid yet, so every game is still free.';
        }
        if ($wantsOn && $d['env'] === 'production' && !str_starts_with(Payments::callbackUrl(), 'https://')) {
            $notes[] = 'Warning: real payments need this site on https so Safaricom can reach the callback address.';
        }
        $msg = 'Payment settings saved. Payments are ' . ($wantsOn ? 'ON (' . $d['env'] . ')' : 'OFF') . '.';

        return redirect()->route('gameset.settings')->with('success', trim($msg . ' ' . implode(' ', $notes)));
    }

    /** "Test connection": asks Safaricom for an access token with the saved keys. */
    public function test(Daraja $daraja)
    {
        if (Payments::env() === 'simulate') {
            return redirect()->route('gameset.settings')->with('success', 'Simulation is selected, so nothing is sent to Safaricom. Choose Sandbox or Production to test the keys.');
        }
        try {
            $daraja->token();
        } catch (PaymentException $e) {
            return redirect()->route('gameset.settings')->withErrors(['Connection test failed: ' . $e->getMessage()]);
        }

        return redirect()->route('gameset.settings')->with('success', 'Connection works: Safaricom accepted the consumer key and secret (' . Payments::env() . ').');
    }

    private function clean(?string $v): ?string
    {
        $v = $v === null ? null : trim($v);

        return $v === '' ? null : $v;
    }

    private function date(?string $v): ?\Illuminate\Support\Carbon
    {
        try {
            return $v ? \Illuminate\Support\Carbon::parse($v) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
