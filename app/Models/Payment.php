<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One pay-per-game order (see the payments migration). Money counts only once it is "paid" or "completed". */
class Payment extends Model
{
    public const PAID = ['paid', 'completed'];

    protected $fillable = [
        'token', 'status', 'mode', 'amount', 'phone', 'payload', 'env', 'merchant_request_id', 'checkout_request_id', 'receipt',
        'result_code', 'result_desc', 'callback', 'stk_sent_at', 'last_query_at', 'paid_at', 'room_id', 'ip',
    ];

    protected $casts = [
        'payload' => 'array', 'callback' => 'array', 'amount' => 'integer', 'result_code' => 'integer',
        'stk_sent_at' => 'datetime', 'last_query_at' => 'datetime', 'paid_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function isPaid(): bool
    {
        return in_array($this->status, self::PAID, true);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['created', 'pending'], true);
    }

    public function scopeMoney($q)
    {
        return $q->whereIn('status', self::PAID);
    }

    /** 2547•••••123 — enough to recognise a customer on screen without showing the whole number. */
    public function maskedPhone(): string
    {
        $p = (string) $this->phone;

        return strlen($p) >= 9 ? substr($p, 0, 4) . '•••••' . substr($p, -3) : '—';
    }

    /** Orders nobody paid for within a day are closed so the open list stays meaningful. */
    public static function expireStale(): void
    {
        static::whereIn('status', ['created', 'pending'])->where('created_at', '<', now()->subDay())->update(['status' => 'expired']);
    }
}
