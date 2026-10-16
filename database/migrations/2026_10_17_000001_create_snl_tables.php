<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin accounts only — players never sign up (party mode has no accounts,
        // online mode is room-code + display name).
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->boolean('is_admin')->default(false);
            $t->rememberToken();
            $t->timestamps();
        });

        // A board = one piece of art + its numbered squares. Squares are placed as
        // % of the image, so any upload works and a re-skin with the same layout
        // needs no re-mapping.
        Schema::create('boards', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('name', 60);
            $t->string('board_image')->nullable();
            $t->unsignedInteger('board_width')->nullable();
            $t->unsignedInteger('board_height')->nullable();
            $t->unsignedSmallInteger('tile_count');
            $t->boolean('is_active')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('tiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('board_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('number');
            $t->string('kind', 20)->default('blank');   // see config/snl.php → tile_kinds
            $t->string('icon', 10)->nullable();
            $t->string('label', 120)->nullable();       // optional fixed text; overrides the card draw
            $t->unsignedSmallInteger('target_number')->nullable(); // snake head / ladder bottom destination
            $t->float('pos_left')->nullable();
            $t->float('pos_top')->nullable();
            $t->float('pos_left_mobile')->nullable();
            $t->float('pos_top_mobile')->nullable();
            $t->timestamps();
            $t->unique(['board_id', 'number']);
        });

        // The deck. Which cards a landing can draw depends on the room's content
        // mode (drinks / family / …) and intensity (1 chill · 2 normal · 3 wild).
        Schema::create('cards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('board_id')->nullable()->constrained()->cascadeOnDelete(); // null = every board
            $t->string('kind', 20);                      // self | pick | everyone | duel | fact | rule
            $t->string('mode', 20)->default('drinks');   // drinks | family | spicy
            $t->unsignedTinyInteger('intensity')->default(1);
            $t->string('text', 220);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['kind', 'mode', 'is_active']);
        });

        Schema::create('rooms', function (Blueprint $t) {
            $t->id();
            $t->string('token', 16)->unique();           // the /play/{token} URL
            $t->string('join_code', 8)->nullable()->unique();
            $t->foreignId('board_id')->constrained();
            $t->string('type', 10)->default('party');    // party (one device) | online
            $t->string('content_mode', 20)->default('drinks');
            $t->unsignedTinyInteger('intensity')->default(2);
            $t->string('status', 12)->default('open');   // open (lobby) | active | completed
            $t->unsignedBigInteger('host_player_id')->nullable();
            $t->unsignedBigInteger('current_player_id')->nullable();
            $t->unsignedBigInteger('winner_player_id')->nullable();
            $t->timestamp('turn_started_at')->nullable();
            $t->unsignedSmallInteger('round')->default(1);
            $t->unsignedTinyInteger('max_players')->default(12);
            $t->string('active_rule', 220)->nullable();  // set by a Rule tile, shown to everyone
            $t->unsignedInteger('event_seq')->default(0);
            $t->json('actions')->nullable();             // rolling log every device replays in order
            $t->json('pending')->nullable();             // a prompt waiting on one player (pick / shield / reroll)
            $t->timestamp('pending_started_at')->nullable();
            $t->string('rematch_token', 16)->nullable();
            $t->unsignedBigInteger('rematch_of')->nullable();
            $t->timestamps();
        });

        Schema::create('players', function (Blueprint $t) {
            $t->id();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->string('name', 24);
            $t->string('color', 9)->default('#f59e0b');
            $t->string('emoji', 8)->nullable();
            $t->unsignedTinyInteger('turn_order')->default(0);
            $t->unsignedSmallInteger('position')->default(0);
            $t->string('status', 10)->default('active'); // active | won | left
            $t->json('powers')->nullable();              // {shield: n, reroll: n}
            $t->json('stats')->nullable();               // {hits, dodges, picks, moves …} for the recap
            $t->unsignedTinyInteger('missed_turns')->default(0);
            $t->unsignedBigInteger('prev_player_id')->nullable(); // rematch lineage
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('tiles');
        Schema::dropIfExists('boards');
        Schema::dropIfExists('users');
    }
};
