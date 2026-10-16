<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $t) {
            $t->boolean('answer')->nullable()->after('text');          // True-or-False cards: the correct answer
            $t->string('explain', 220)->nullable()->after('answer');   // shown after the reveal
            $t->unsignedTinyInteger('penalty')->default(1)->after('explain'); // how many "units" a wrong answer / lost bluff costs
            $t->unsignedSmallInteger('timer')->nullable()->after('penalty');  // Spotlight: seconds on the clock
        });

        // Slot words for templated cards: "Recommend a movie for {scene}" draws a fresh {scene} every time.
        Schema::create('word_lists', function (Blueprint $t) {
            $t->id();
            $t->string('slot', 24);                       // scene, moment, vibe, topic, …
            $t->string('mode', 20)->default('all');       // all | drinks | family | lovers
            $t->unsignedTinyInteger('intensity')->default(1);
            $t->string('text', 90);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['slot', 'mode', 'is_active']);
        });

        // The couples content mode is now "lovers" — "spicy" is the top intensity level.
        DB::table('cards')->where('mode', 'spicy')->update(['mode' => 'lovers']);
        DB::table('rooms')->where('content_mode', 'spicy')->update(['content_mode' => 'lovers']);
    }

    public function down(): void
    {
        Schema::dropIfExists('word_lists');
        Schema::table('cards', function (Blueprint $t) {
            $t->dropColumn(['answer', 'explain', 'penalty', 'timer']);
        });
    }
};
