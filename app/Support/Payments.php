<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Pay-per-game settings and helpers. An admin sets everything in GameSet → Settings → Payments:
 * the on/off switch, the Daraja (M-Pesa) keys, and which modes cost money and how much.
 * Money only flows when the switch is on AND the setup is complete, so a half-filled form can never charge (or silently not charge) anyone.
 */
class Payments
{
    /** simulate = no real money (for trying the whole flow), sandbox = Safaricom's test system, production = real money. */
    public const ENVS = ['simulate', 'sandbox', 'production'];

    public static function env(): string
    {
        $e = (string) Setting::get('pay.env', 'simulate');

        return in_array($e, self::ENVS, true) ? $e : 'simulate';
    }

    /** @return array{key: string, secret: string, shortcode: string, passkey: string, type: string, party_b: string} */
    public static function daraja(): array
    {
        $shortcode = trim((string) Setting::get('pay.shortcode', ''));
        $type = Setting::get('pay.txn_type', 'CustomerPayBillOnline') === 'CustomerBuyGoodsOnline' ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline';

        return [
            'key' => trim((string) Setting::get('pay.consumer_key', '')),
            'secret' => (string) Setting::secret('pay.consumer_secret'),
            'shortcode' => $shortcode,
            'passkey' => (string) Setting::secret('pay.passkey'),
            'type' => $type,
            // a Till number (Buy Goods) is the party that is paid; for a Paybill it is the shortcode itself
            'party_b' => $type === 'CustomerBuyGoodsOnline' ? (trim((string) Setting::get('pay.till', '')) ?: $shortcode) : $shortcode,
        ];
    }

    /** Is the Daraja setup complete enough to take a payment? (Simulation needs nothing.) */
    public static function ready(): bool
    {
        if (self::env() === 'simulate') {
            return true;
        }
        $d = self::daraja();

        return $d['key'] !== '' && $d['secret'] !== '' && $d['shortcode'] !== '' && $d['passkey'] !== '';
    }

    public static function enabled(): bool
    {
        return (string) Setting::get('pay.enabled', '0') === '1' && self::ready();
    }

    /** @return array<string, int> mode key => price in KES, only for modes that are paid */
    public static function prices(): array
    {
        $raw = json_decode((string) Setting::get('pay.prices', '{}'), true);
        $out = [];
        foreach ((array) $raw as $mode => $price) {
            if (isset(config('snl.modes')[$mode]) && (int) $price >= 1) {
                $out[$mode] = (int) $price;
            }
        }

        return $out;
    }

    /** The price of one game in this mode, or null when it is free (or payments are off). */
    public static function priceFor(string $mode): ?int
    {
        return self::enabled() ? (self::prices()[$mode] ?? null) : null;
    }

    /** What the setup page may show (never any key). */
    public static function publicConfig(): array
    {
        return ['enabled' => self::enabled(), 'prices' => self::enabled() ? self::prices() : [], 'simulate' => self::enabled() && self::env() === 'simulate'];
    }

    /** The secret in the callback address, so only Safaricom (who is told the full address) can report a payment. Made on first use. */
    public static function callbackSecret(): string
    {
        $s = (string) Setting::get('pay.callback_secret', '');
        if ($s === '') {
            $s = Str::random(40);
            Setting::put('pay.callback_secret', $s);
        }

        return $s;
    }

    public static function callbackUrl(): string
    {
        return url('/mpesa/callback/' . self::callbackSecret());
    }

    /** 07XX…, 01XX…, 7XX…, +2547XX… → 2547XXXXXXXX (Safaricom lines), or null when it is not a Safaricom number. */
    public static function normalizePhone(string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', $raw);
        if (preg_match('/^(?:254|0)?([71]\d{8})$/', $d, $m)) {
            return '254' . $m[1];
        }

        return null;
    }

    /**
     * Applies the outcome of a payment (from Safaricom's callback, or from asking M-Pesa directly). Safe to call any number of times:
     * a payment that is already settled is never changed. Only a successful result of at least the order amount marks it paid.
     */
    public static function applyResult(Payment $p, int $code, string $desc, array $meta = [], ?array $raw = null): Payment
    {
        if ($p->isPaid()) {
            return $p;
        }
        $p->result_code = $code;
        $p->result_desc = mb_substr($desc, 0, 200);
        if ($raw !== null) {
            $p->callback = $raw;
        }
        if ($code === 0) {
            $paid = (float) ($meta['Amount'] ?? $p->amount);
            if ($paid + 0.001 < $p->amount) {
                $p->status = 'failed'; // the customer paid less than the price: never open the room, an admin can review it
                $p->result_desc = mb_substr("Amount mismatch: paid {$paid}, expected {$p->amount}", 0, 200);
            } else {
                $p->status = 'paid';
                $p->receipt = isset($meta['MpesaReceiptNumber']) ? mb_substr((string) $meta['MpesaReceiptNumber'], 0, 30) : $p->receipt;
                $p->paid_at = now();
                if (!empty($meta['PhoneNumber'])) {
                    $p->phone = mb_substr((string) $meta['PhoneNumber'], 0, 15);
                }
            }
        } else {
            $p->status = $code === 1032 ? 'cancelled' : 'failed'; // 1032: the customer closed the prompt
        }
        $p->save();

        return $p;
    }
}
