<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Daraja;
use App\Services\GameException;
use App\Services\PaymentException;
use App\Support\Payments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pay-per-game checkout. The host finishes setting up the game, pays with an M-Pesa prompt, and only THEN is the room created:
 *   /new (store)  →  an order is saved  →  /checkout/{token}  →  phone number  →  M-Pesa prompt  →  paid  →  room created  →  /play/{room}
 * The price is fixed by the server when the order is made; the saved setup cannot be changed after that.
 */
class CheckoutController extends Controller
{
    public function __construct(private Daraja $daraja)
    {
    }

    public function show(Payment $payment)
    {
        Payment::expireStale();
        $payment->refresh();
        if ($payment->room_id) { // already paid for and created: just go in
            return redirect()->route('play.show', \App\Models\Room::whereKey($payment->room_id)->value('token') ?? '');
        }
        $p = $payment->payload;

        return view('checkout', [
            'order' => $payment,
            'mode' => config("snl.modes.{$payment->mode}"),
            'modeKey' => $payment->mode,
            'who' => ($p['type'] ?? '') === 'party' ? 'a party game for ' . count($p['names'] ?? []) . ' players' : 'an online room',
            'simulate' => $payment->env === 'simulate',
            'back' => route('setup', ['type' => $p['type'] ?? 'party']),
            'phoneHint' => preg_replace('/^254/', '0', (string) $payment->phone),
            'state' => $this->view($payment),
        ]);
    }

    /** Sends the M-Pesa prompt to the number the host typed. */
    public function pay(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->isPaid()) {
            return response()->json($this->view($payment));
        }
        if (!in_array($payment->status, ['created', 'failed', 'cancelled', 'pending'], true)) {
            return response()->json(['error' => 'This order has expired. Please set the game up again.', 'status' => $payment->status], 410);
        }
        if ($payment->status === 'pending' && $payment->stk_sent_at && $payment->stk_sent_at->gt(now()->subSeconds(25))) {
            return response()->json(['error' => 'A prompt was just sent. Check your phone and enter your M-Pesa PIN.'] + $this->view($payment), 429);
        }
        $request->validate(['phone' => 'required|string|max:20']);
        $phone = Payments::normalizePhone((string) $request->input('phone'));
        if (!$phone) {
            return response()->json(['error' => 'Enter a Safaricom number like 0712 345 678 (or 0112 345 678).'], 422);
        }
        if ($payment->env !== Payments::env()) { // the payment mode was changed in GameSet after this order was made
            return response()->json(['error' => 'Payments were reconfigured. Please set the game up again.'], 409);
        }

        try {
            $sent = $this->daraja->stkPush($payment, $phone);
        } catch (PaymentException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
        $payment->forceFill([
            'status' => 'pending', 'phone' => $phone, 'merchant_request_id' => $sent['merchant'], 'checkout_request_id' => $sent['checkout'],
            'stk_sent_at' => now(), 'result_code' => null, 'result_desc' => null, 'last_query_at' => null,
        ])->save();

        return response()->json($this->view($payment) + ['message' => $sent['message']]);
    }

    /** The page asks this every couple of seconds while the customer is entering their PIN. */
    public function status(Payment $payment): JsonResponse
    {
        $this->reconcile($payment);

        return response()->json($this->view($payment));
    }

    /** Payment confirmed → create the room from the saved setup. Safe to call more than once: it never makes two rooms. */
    public function finish(Request $request, Payment $payment): JsonResponse
    {
        $this->reconcile($payment);
        $payment->refresh();
        if (!$payment->isPaid()) {
            return response()->json(['error' => 'Payment has not been confirmed yet.'] + $this->view($payment), 409);
        }
        try {
            $room = DB::transaction(function () use ($request, $payment) {
                $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
                if ($locked->room_id) {
                    return \App\Models\Room::find($locked->room_id);
                }
                $room = app(RoomController::class)->createGame($request, $locked->payload);
                $locked->forceFill(['room_id' => $room->id, 'status' => 'completed'])->save();

                return $room;
            });
        } catch (GameException|\Illuminate\Validation\ValidationException $e) {
            Log::warning("Paid order {$payment->id} could not create its room: " . $e->getMessage());

            return response()->json(['error' => 'Your payment went through but the game could not be created (' . ($e instanceof GameException ? $e->getMessage() : 'a setup problem') . '). Please contact us and quote the code ' . ($payment->receipt ?: $payment->token) . '.'], 500);
        }

        return response()->json(['redirect' => route('play.show', $room->token)]);
    }

    /** What the checkout page needs to know. */
    private function view(Payment $p): array
    {
        $msg = match ($p->status) {
            'pending' => 'Check your phone and enter your M-Pesa PIN.',
            'paid', 'completed' => 'Payment received. Creating your game…',
            'cancelled' => 'The payment was cancelled on the phone. You can try again.',
            'failed' => $p->result_desc ? 'The payment did not go through: ' . $p->result_desc : 'The payment did not go through.',
            'expired' => 'This order has expired. Please set the game up again.',
            default => '',
        };

        return ['status' => $p->status, 'message' => $msg, 'amount' => $p->amount, 'receipt' => $p->isPaid() ? $p->receipt : null];
    }

    /**
     * Keeps an open order up to date without waiting for Safaricom's callback (which can be late or lost):
     * in simulation the pretend payment settles after a few seconds; for real payments we ask M-Pesa directly after a short wait.
     */
    private function reconcile(Payment $p): void
    {
        if ($p->status !== 'pending') {
            return;
        }
        if ($p->env === 'simulate') {
            if ($p->stk_sent_at && $p->stk_sent_at->lt(now()->subSeconds(4))) { // a number ending in 0 plays the customer who cancels
                $ok = !str_ends_with((string) $p->phone, '0');
                Payments::applyResult($p, $ok ? 0 : 1032, $ok ? 'Simulation: paid.' : 'Simulation: cancelled by the customer.',
                    $ok ? ['Amount' => $p->amount, 'MpesaReceiptNumber' => 'SIM' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 7))] : []);
            }

            return;
        }
        $waited = $p->stk_sent_at ? $p->stk_sent_at->diffInSeconds(now()) : 0;
        $last = $p->last_query_at ? $p->last_query_at->diffInSeconds(now()) : 999;
        if ($waited >= 20 && $last >= 8) {
            $p->forceFill(['last_query_at' => now()])->save();
            $r = $this->daraja->stkQuery($p);
            if ($r['done']) {
                Payments::applyResult($p, $r['code'], $r['desc'] ?? '');
            }
        }
    }
}
