<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Support\Payments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Safaricom posts the result of every M-Pesa prompt here (the address contains a secret, set in GameSet → Settings → Payments).
 * We always answer "accepted" so Safaricom does not retry; whether the money counts is decided by Payments::applyResult().
 */
class MpesaCallbackController extends Controller
{
    public function __invoke(Request $request, string $secret): JsonResponse
    {
        $accepted = response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        if (!hash_equals(Payments::callbackSecret(), $secret)) {
            Log::warning('M-Pesa callback with a wrong secret from ' . $request->ip());

            return $accepted; // say nothing useful to anyone guessing
        }
        $cb = $request->input('Body.stkCallback');
        if (!is_array($cb) || empty($cb['CheckoutRequestID'])) {
            return $accepted;
        }
        $payment = Payment::where('checkout_request_id', (string) $cb['CheckoutRequestID'])->first();
        if (!$payment) {
            Log::info('M-Pesa callback for an unknown request ' . $cb['CheckoutRequestID']);

            return $accepted;
        }
        $meta = [];
        foreach ((array) ($cb['CallbackMetadata']['Item'] ?? []) as $item) {
            if (isset($item['Name'])) {
                $meta[$item['Name']] = $item['Value'] ?? null;
            }
        }
        Payments::applyResult($payment, (int) ($cb['ResultCode'] ?? 1), (string) ($cb['ResultDesc'] ?? ''), $meta, $request->input('Body'));

        return $accepted;
    }
}
