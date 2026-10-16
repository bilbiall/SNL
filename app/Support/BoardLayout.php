<?php

namespace App\Support;

/**
 * Starting points for a freshly created board. The admin then drags every
 * numbered square onto the real art — these only make the first screen sensible.
 */
class BoardLayout
{
    /** Tiles zig-zag bottom-to-top across the image (row 1 left→right, row 2 right→left…).
     *  @return array<int, array{0: float, 1: float}> number => [left%, top%] */
    public static function serpentine(int $n): array
    {
        $cols = max(4, (int) ceil(sqrt($n * 1.5)));
        $rows = (int) ceil($n / $cols);
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $row = intdiv($i, $cols);
            $col = $i % $cols;
            if ($row % 2 === 1) {
                $col = $cols - 1 - $col;
            }
            $left = 8 + $col * (84 / max(1, $cols - 1));
            $top = $rows > 1 ? 90 - $row * (80 / ($rows - 1)) : 50;
            $out[$i + 1] = [round($left, 2), round($top, 2)];
        }

        return $out;
    }

    /**
     * A balanced starter mix of tile kinds for an n-tile board: a few ladders and snakes
     * (placed by proportion so any board size works) and a repeating party sequence.
     * @return array<int, array{kind: string, target: ?int}> number => spec
     */
    public static function starterKinds(int $n): array
    {
        $cycle = ['self', 'blank', 'pick', 'fact', 'everyone', 'blank', 'spin', 'self', 'pick', 'power',
                  'rule', 'blank', 'duel', 'fact', 'swap', 'blank'];
        $out = [];
        for ($i = 1; $i <= $n; $i++) {
            $out[$i] = ['kind' => ($i === 1 || $i === $n) ? 'blank' : $cycle[($i - 2) % count($cycle)], 'target' => null];
        }

        // [from, to] as fractions of the board — ladders climb, snakes slide.
        $ladders = [[0.07, 0.23], [0.15, 0.50], [0.33, 0.63], [0.47, 0.78], [0.67, 0.88]];
        $snakes  = [[0.28, 0.12], [0.57, 0.38], [0.72, 0.52], [0.85, 0.62], [0.95, 0.75]];
        $used = [];
        $place = function (array $pairs, string $kind) use (&$out, &$used, $n) {
            foreach ($pairs as [$a, $b]) {
                $from = max(2, min($n - 2, (int) round($a * $n)));
                $to = max(2, min($n - 1, (int) round($b * $n)));
                if ($from === $to || isset($used[$from]) || isset($used[$to])) {
                    continue;
                }
                $out[$from] = ['kind' => $kind, 'target' => $to];
                $used[$from] = $used[$to] = true;
            }
        };
        $place($ladders, 'ladder_bottom');
        $place($snakes, 'snake_head');

        return $out;
    }
}
