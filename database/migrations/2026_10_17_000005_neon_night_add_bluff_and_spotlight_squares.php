<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Turns a handful of Neon Night squares into Fact-or-Bluff and Spotlight squares (and one more True-or-False). */
    private const CHANGES = [
        'bluff'     => [27, 48, 74],
        'spotlight' => [16, 33, 54, 60, 78],
        'fact'      => [57],
    ];

    public function up(): void
    {
        $board = DB::table('boards')->where('slug', 'snl-night')->value('id');
        if (!$board) {
            return; // fresh installs get these straight from the seeder data
        }
        foreach (self::CHANGES as $kind => $numbers) {
            DB::table('tiles')->where('board_id', $board)->whereIn('number', $numbers)->update(['kind' => $kind]);
        }
    }

    public function down(): void
    {
        $board = DB::table('boards')->where('slug', 'snl-night')->value('id');
        if ($board) {
            DB::table('tiles')->where('board_id', $board)->whereIn('number', [27, 48])->update(['kind' => 'fact']);
            DB::table('tiles')->where('board_id', $board)->whereIn('number', [33, 54, 78])->update(['kind' => 'self']);
            DB::table('tiles')->where('board_id', $board)->whereIn('number', [16, 60, 74, 57])->update(['kind' => 'blank']);
        }
    }
};
