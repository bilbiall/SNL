<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Re-maps the Neon Night squares so each one matches the icon printed on the board art
 * (☝ you · pointing finger / person→person pick · hands-up everyone · 💡 true-or-false · ⟳ swap · scroll rule ·
 * swords duel · wheel spin · ⚡ power-up · ladder+hearts "Love ladder"). Snakes and ladders are left alone.
 */
return new class extends Migration
{
    private const KINDS = 'bsbebLbbmLbbxbrbdbfwwbbLbbobeSbmbpbbLbbfbbbSbLpfbbhbbspubrbbdbhwbSbhxpSeeombpbSb';
    private const MAP = ['b' => 'blank', 's' => 'self', 'p' => 'pick', 'e' => 'everyone', 'd' => 'duel', 'w' => 'swap', 'f' => 'fact',
        'r' => 'rule', 'm' => 'spin', 'x' => 'power', 'u' => 'bluff', 'o' => 'spotlight', 'h' => 'heart', 'L' => 'ladder_bottom', 'S' => 'snake_head'];

    public function up(): void
    {
        $board = DB::table('boards')->where('slug', 'snl-night')->first();
        if (!$board) {
            return;
        }
        foreach (str_split(self::KINDS) as $i => $code) {
            DB::table('tiles')->where('board_id', $board->id)->where('number', $i + 1)
                ->update(['kind' => self::MAP[$code], 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
    }
};
