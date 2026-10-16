<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Players now pick a retro pixel avatar ("m3-4") instead of an emoji/icon. */
    public function up(): void
    {
        Schema::table('players', function (Blueprint $t) {
            $t->renameColumn('emoji', 'avatar');
        });
        Schema::table('players', function (Blueprint $t) {
            $t->string('avatar', 12)->nullable()->change();
        });
        // Anything from before the roster existed gets a sensible default.
        DB::table('players')->where(function ($q) {
            $q->whereNull('avatar')->orWhere('avatar', 'not like', '%-%');
        })->update(['avatar' => 'm1-3']);
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $t) {
            $t->renameColumn('avatar', 'emoji');
        });
    }
};
