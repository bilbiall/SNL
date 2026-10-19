<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Team mode (teammates share a token and a penalty) and secret missions (a private goal per player, online). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $t) {
            $t->boolean('team_mode')->default(false)->after('is_public');
        });
        Schema::table('players', function (Blueprint $t) {
            $t->unsignedTinyInteger('team')->nullable()->after('turn_order');   // 1, 2, 3 … (teams of two); null = playing solo
            $t->json('mission')->nullable()->after('stats');                    // the player's current secret mission + how many they have finished
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $t) {
            $t->dropColumn(['team', 'mission']);
        });
        Schema::table('rooms', function (Blueprint $t) {
            $t->dropColumn('team_mode');
        });
    }
};
