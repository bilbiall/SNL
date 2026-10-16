<?php

/*
| "Neon Night" — the 80-square board art in public/img/boards/snl-night.webp (1264×848).
|
| rows: bottom row first — [squares in row, x of the row's FIRST (lowest-numbered) square, x of its LAST square, y],
|       all in image pixels. Squares between are spaced evenly (same idea as GameSet's Quick layout).
| kinds: one code per square, 1…80:
|   b free · s you-do-it · p pick-a-friend · e everyone · d duel · w swap · f true-or-false · r rule
|   m mystery spin · x power-up · u fact-or-bluff · o spotlight · L ladder bottom · S snake head
| ladders / snakes: from => to (read off the artwork).
*/
return [
    'slug'   => 'snl-night',
    'name'   => 'Neon Night',
    'image'  => '/img/boards/snl-night.webp',
    'width'  => 1264,
    'height' => 848,
    'rows' => [
        [12,  285, 1205, 735],
        [14, 1190,  130, 595],
        [13,  135, 1160, 458],
        [14, 1180,   95, 322],
        [14,  170, 1200, 192],
        [13, 1160,  215,  65],
    ],
    'kinds' => 'bsbeeLbbmLbb' . 'xsrodbfwwssLbe' . 'ubeSpmopsxLbd' . 'fwbbSfLpubexbe' . 'opdfrbodbxwbSb' . 'xxpSeeumbpoSb',
    'ladders' => [6 => 35, 10 => 39, 24 => 52, 37 => 65, 46 => 76],
    'snakes'  => [79 => 49, 71 => 62, 66 => 41, 44 => 35, 30 => 22],
];
