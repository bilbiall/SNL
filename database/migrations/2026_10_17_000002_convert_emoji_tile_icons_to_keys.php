<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The UI now draws game icons, not emoji. Tiles seeded with the old finish-flag emoji get the matching icon key. */
    public function up(): void
    {
        DB::table('tiles')->where('icon', '🏁')->update(['icon' => 'flag']);
    }

    public function down(): void
    {
        DB::table('tiles')->where('icon', 'flag')->update(['icon' => '🏁']);
    }
};
