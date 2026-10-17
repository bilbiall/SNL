<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remembers which cards / words a game has already shown (a "shuffle bag"), plus the last free-square events,
 * so the same card never turns up twice in a row and every deck is used up before it repeats.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $t) {
            $t->json('deck_state')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $t) {
            $t->dropColumn('deck_state');
        });
    }
};
