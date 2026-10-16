<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Support\BoardLayout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardSeeder extends Seeder
{
    /** A starter board on placeholder art, so the game is playable on day one.
     *  The admin replaces the art and re-maps the squares in GameSet → Boards. */
    public function run(): void
    {
        if (Board::exists()) {
            return;
        }
        $n = 60;
        $board = Board::create([
            'slug' => 'starter-board', 'name' => 'Starter Board', 'tile_count' => $n,
            'board_image' => config('snl.default_board_art'), 'board_width' => 1264, 'board_height' => 848,
            'is_active' => true, 'sort_order' => 1,
        ]);

        $now = now();
        $kinds = BoardLayout::starterKinds($n);
        $rows = [];
        foreach (BoardLayout::serpentine($n) as $number => [$left, $top]) {
            $spec = $kinds[$number];
            $rows[] = [
                'board_id' => $board->id, 'number' => $number, 'kind' => $spec['kind'], 'target_number' => $spec['target'],
                'icon' => $number === $n ? 'flag' : null,
                'label' => $number === $n ? 'Finish — reach this square to win' : null,
                'pos_left' => $left, 'pos_top' => $top, 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        DB::table('tiles')->insert($rows);
    }
}
