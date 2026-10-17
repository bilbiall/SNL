<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Where players wait before their first roll — a rectangle on the board art (percent of the image),
 * set in GameSet → Boards. NULL = stack on square 1 like before. The seeded Neon Night board gets the START sign.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boards', function (Blueprint $t) {
            $t->json('start_zone')->nullable()->after('is_active');
        });
        DB::table('boards')->where('slug', 'snl-night')->update(['start_zone' => json_encode(['left' => 1.6, 'top' => 76.5, 'width' => 12.8, 'height' => 16.5])]);
    }

    public function down(): void
    {
        Schema::table('boards', function (Blueprint $t) {
            $t->dropColumn('start_zone');
        });
    }
};
