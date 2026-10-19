<?php

namespace App\Services;

use App\Models\Payment;
use App\Support\Payments;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Safaricom Daraja: M-Pesa Express (STK push). Three environments, chosen in GameSet → Settings → Payments:
 *   simulate    nothing is sent anywhere: the checkout is exercised end to end with pretend money
 *   sandbox     Safaricom's test system (https://sandbox.safaricom.co.ke)
 *   production  real money (https://api.safaricom.co.ke)
 * Errors are turned into plain messages; keys and tokens are never written to the log.
 */
class Daraja
{
    private function base(): string
    {
        return Payments::env() === 'production' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
    }

    /** OAuth access token (valid for an hour, kept for 50 minutes). Also used by "Test connection" in GameSet. */
    public function token(): string
    {
        $d = Payments::daraja();
        if ($d['key'] === '' || $d['secret'] === '') {
            throw new PaymentException('The consumer key and secret are not filled in.');
        }
        $cacheKey = 'daraja_token_' . Payments::env() . '_' . md5($d['key'] . $d['secret']);
        if ($t = Cache::get($cacheKey)) {
            return $t;
        }
        try {
            $r = Http::withBasicAuth($d['key'], $d['secret'])->acceptJson()->timeout(20)->get($this->base() . '/oauth/v1/generate', ['grant_type' => 'client_credentials']);
        } catch (\Throwable $e) {
            Log::warning('Daraja token request failed: ' . $e->getMessage());
            throw new PaymentException('Could not reach Safaricom. Check the internet connection of the server and try again.');
        }
        $token = $r->json('access_token');
        if (!$r->successful() || !$token) {
            Log::warning('Daraja token refused: HTTP ' . $r->status());
            throw new PaymentException('Safaricom did not accept the consumer key and secret (' . Payments::env() . '). Check they match the environment.');
        }
        Cache::put($cacheKey, $token, now()->addMinutes(50));

        return $token;
    }

    /** Sends the M-Pesa prompt to the customer's phone. @return array{merchant: string, checkout: string, message: string} */
    public function stkPush(Payment $p, string $phone): array
    {
        if (Payments::env() === 'simulate') {
            return ['merchant' => 'SIM-' . bin2hex(random_bytes(4)), 'checkout' => 'ws_CO_SIM_' . bin2hex(random_bytes(6)), 'message' => 'Simulation: pretend prompt sent.'];
        }
        $d = Payments::daraja();
        $ts = now('Africa/Nairobi')->format('YmdHis');
        $body = [
            'BusinessShortCode' => $d['shortcode'],
            'Password' => base64_encode($d['shortcode'] . $d['passkey'] . $ts),
            'Timestamp' => $ts,
            'TransactionType' => $d['type'],
            'Amount' => (int) $p->amount,
            'PartyA' => $phone,
            'PartyB' => $d['party_b'],
            'PhoneNumber' => $phone,
            'CallBackURL' => Payments::callbackUrl(),
            'AccountReference' => 'SnakesLiquor',
            'TransactionDesc' => 'Game ' . mb_substr($p->mode, 0, 8),
        ];
        try {
            $r = Http::withToken($this->token())->acceptJson()->timeout(30)->post($this->base() . '/mpesa/stkpush/v1/processrequest', $body);
        } catch (PaymentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('Daraja STK push failed: ' . $e->getMessage());
            throw new PaymentException('Could not reach Safaricom. Please try again in a moment.');
        }
        $j = $r->json() ?: [];
        if (!$r->successful() || (string) ($j['ResponseCode'] ?? '') !== '0' || empty($j['CheckoutRequestID'])) {
            Log::warning('Daraja STK push refused: HTTP ' . $r->status() . ' ' . json_encode(array_intersect_key($j, array_flip(['errorCode', 'errorMessage', 'ResponseCode', 'ResponseDescription']))));
            throw new PaymentException('M-Pesa could not send the prompt: ' . ($j['errorMessage'] ?? $j['ResponseDescription'] ?? 'please check the number and try again') . '.');
        }

        return ['merchant' => (string) ($j['MerchantRequestID'] ?? ''), 'checkout' => (string) $j['CheckoutRequestID'], 'message' => (string) ($j['CustomerMessage'] ?? '')];
    }

    /**
     * Asks M-Pesa what happened to a prompt (used when the callback is late or never arrives).
     * @return array{done: bool, code?: int, desc?: string}  done=false while the customer has not answered yet
     */
    public function stkQuery(Payment $p): array
    {
        if (Payments::env() === 'simulate' || !$p->checkout_request_id) {
            return ['done' => false];
        }
        $d = Payments::daraja();
        $ts = now('Africa/Nairobi')->format('YmdHis');
        try {
            $r = Http::withToken($this->token())->acceptJson()->timeout(20)->post($this->base() . '/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => $d['shortcode'], 'Password' => base64_encode($d['shortcode'] . $d['passkey'] . $ts),
                'Timestamp' => $ts, 'CheckoutRequestID' => $p->checkout_request_id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Daraja STK query failed: ' . $e->getMessage());

            return ['done' => false];
        }
        $j = $r->json() ?: [];
        if (!isset($j['ResultCode'])) {
            return ['done' => false]; // "the transaction is being processed" comes back as an error until the customer answers
        }

        return ['done' => true, 'code' => (int) $j['ResultCode'], 'desc' => (string) ($j['ResultDesc'] ?? '')];
    }
}
