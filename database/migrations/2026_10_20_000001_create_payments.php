<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pay-per-game orders. A paid mode does not create its room straight away: the finished setup is saved here as an order,
 * the host pays with an M-Pesa prompt (STK push), and the room is created from the saved setup only after the payment succeeds.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->string('token', 40)->unique();                 // the unguessable id in the checkout link
            $t->string('status', 16)->default('created');      // created · pending · paid · completed · failed · cancelled · expired
            $t->string('mode', 20);                            // the content mode being bought (lovers, girlies …)
            $t->unsignedInteger('amount');                     // KES, fixed by the server from the price list when the order is made
            $t->string('phone', 15)->nullable();               // 2547XXXXXXXX, set when the host taps Pay
            $t->json('payload');                               // the whole game setup, used to create the room after payment
            $t->string('env', 12);                             // simulate · sandbox · production (so test money is never mixed up with real money)
            $t->string('merchant_request_id', 60)->nullable();
            $t->string('checkout_request_id', 60)->nullable()->index();
            $t->string('receipt', 30)->nullable()->index();    // the M-Pesa confirmation code, e.g. NLJ7RT61SV
            $t->integer('result_code')->nullable();
            $t->string('result_desc', 200)->nullable();
            $t->json('callback')->nullable();                  // the raw callback Safaricom sent, kept for audit
            $t->timestamp('stk_sent_at')->nullable();
            $t->timestamp('last_query_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->unsignedBigInteger('room_id')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
            $t->index(['status', 'created_at']);
            $t->index(['mode', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
