<?php

namespace Database\Seeders;

use App\Models\Board;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardSeeder extends Seeder
{
    private const KINDS = [
        'b' => 'blank', 's' => 'self', 'p' => 'pick', 'e' => 'everyone', 'd' => 'duel', 'w' => 'swap', 'f' => 'fact',
        'r' => 'rule', 'm' => 'spin', 'x' => 'power', 'u' => 'bluff', 'o' => 'spotlight', 'L' => 'ladder_bottom', 'S' => 'snake_head',
    ];

    /**
     * Seeds the "Neon Night" board: art, 80 squares placed on it, square types, and the artwork's
     * 5 ladders + 5 snakes. Safe to run again (it only creates the board once). The first time it runs,
     * it becomes the only board players can pick; later boards come from GameSet.
     */
    public function run(): void
    {
        $spec = require __DIR__ . '/data/snl-night.php';
        if (Board::where('slug', $spec['slug'])->exists()) {
            return;
        }

        $codes = str_split($spec['kinds']);
        $count = count($codes);
        $rowTotal = array_sum(array_column($spec['rows'], 0));
        if ($count !== $rowTotal) {
            throw new \RuntimeException("Board spec mismatch: {$count} square types but rows add up to {$rowTotal}.");
        }

        DB::transaction(function () use ($spec, $codes, $count) {
            Board::query()->update(['is_active' => false]); // the new board replaces any earlier placeholder

            $board = Board::create([
                'slug' => $spec['slug'], 'name' => $spec['name'], 'board_image' => $spec['image'],
                'board_width' => $spec['width'], 'board_height' => $spec['height'], 'tile_count' => $count,
                'is_active' => true, 'sort_order' => 1,
            ]);

            $now = now();
            $rows = [];
            $n = 1;
            foreach ($spec['rows'] as [$cnt, $x1, $x2, $y]) {
                for ($k = 0; $k < $cnt; $k++, $n++) {
                    $x = $cnt > 1 ? $x1 + ($x2 - $x1) * $k / ($cnt - 1) : $x1;
                    $kind = self::KINDS[$codes[$n - 1]];
                    $rows[] = [
                        'board_id' => $board->id, 'number' => $n, 'kind' => $kind,
                        'target_number' => $spec['ladders'][$n] ?? $spec['snakes'][$n] ?? null,
                        'icon' => $n === $count ? 'flag' : null, 'label' => null,
                        'pos_left' => round($x / $spec['width'] * 100, 2), 'pos_top' => round($y / $spec['height'] * 100, 2),
                        'created_at' => $now, 'updated_at' => $now,
                    ];
                }
            }
            DB::table('tiles')->insert($rows);
        });
    }
}
