<?php

/*
|--------------------------------------------------------------------------
| Snakes & Liquor — game configuration
|--------------------------------------------------------------------------
| Everything the engine, the setup screen and the GameSet admin need to agree
| on lives here. Card TEXT lives in the database (admin-editable); this file
| only defines the vocabulary.
|
| Every `icon` below is a key from resources/icons.json (compiled into
| public/icons.svg by `npm run icons`). No emoji anywhere in the game UI.
*/

return [

    'name' => env('APP_NAME', 'Snakes & Liquor'),

    /*
    | Content modes. One board, one set of rules — only the deck changes.
    | `adult` modes force an 18+ confirmation. `enabled => false` shows the mode
    | as "coming soon" and blocks it server-side until it is switched on.
    */
    'modes' => [
        'drinks' => ['label' => 'Drinks', 'icon' => 'cheers',   'adult' => true,  'enabled' => true,
                     'blurb' => 'Shots, sips and forfeits. 18+ only — please drink responsibly.'],
        'family' => ['label' => 'Family', 'icon' => 'balloons', 'adult' => false, 'enabled' => true,
                     'blurb' => 'Zero alcohol. Silly actions, facts and challenges everyone can play.'],
        'lovers' => ['label' => 'Lovers', 'icon' => 'heart',    'adult' => true,  'enabled' => false,
                     'blurb' => 'For couples. Coming soon.'],
    ],

    'intensities' => [
        1 => ['label' => 'Chill',  'icon' => 'thumb'],
        2 => ['label' => 'Normal', 'icon' => 'star'],
        3 => ['label' => 'Spicy',  'icon' => 'fire'],
    ],

    /*
    | Tile kinds. `card` = which card kind a landing draws (null = no card).
    */
    'tile_kinds' => [
        'blank'         => ['label' => 'Free space',     'icon' => 'hourglass', 'card' => null,       'color' => '#64748b', 'help' => 'Nothing happens — breathe.'],
        'self'          => ['label' => 'You do it',      'icon' => 'self',      'card' => 'self',     'color' => '#f59e0b', 'help' => 'The roller does what the card says (e.g. take a shot).'],
        'pick'          => ['label' => 'Pick a friend',  'icon' => 'pick',      'card' => 'pick',     'color' => '#ec4899', 'help' => 'The roller chooses who does what the card says.'],
        'everyone'      => ['label' => 'Everyone',       'icon' => 'everyone',  'card' => 'everyone', 'color' => '#10b981', 'help' => 'Every player does what the card says.'],
        'duel'          => ['label' => 'Duel',           'icon' => 'duel',      'card' => 'duel',     'color' => '#ef4444', 'help' => 'The roller picks an opponent; both roll, the lower roll loses.'],
        'swap'          => ['label' => 'Swap places',    'icon' => 'swap',      'card' => null,       'color' => '#8b5cf6', 'help' => 'The roller picks a player and they trade board positions.'],
        'fact'          => ['label' => 'True or false',  'icon' => 'fact',      'card' => 'fact',     'color' => '#06b6d4', 'help' => 'The game states a fact — the roller says true or false. Wrong answer = a penalty.'],
        'bluff'         => ['label' => 'Fact or bluff',  'icon' => 'jester',    'card' => 'bluff',    'color' => '#14b8a6', 'help' => 'The roller states a fact (true or made up). Anyone can call bluff — then it must be proven, or someone drinks.'],
        'spotlight'     => ['label' => 'Spotlight',      'icon' => 'mic',       'card' => 'spotlight','color' => '#fb923c', 'help' => 'A creative prompt on the clock — a movie for the moment, a song, a hot take. Fail it and take a penalty.'],
        'rule'          => ['label' => 'New rule',       'icon' => 'rule',      'card' => 'rule',     'color' => '#a855f7', 'help' => 'Sets a table rule that stays until the next rule tile.'],
        'spin'          => ['label' => 'Mystery spin',   'icon' => 'mystery',   'card' => null,       'color' => '#f43f5e', 'help' => 'Spins for a random tile effect.'],
        'power'         => ['label' => 'Power-up',       'icon' => 'power',     'card' => null,       'color' => '#eab308', 'help' => 'Gives the roller a Shield or a Reroll.'],
        'snake_head'    => ['label' => 'Snake head',     'icon' => 'snake',     'card' => null,       'color' => '#dc2626', 'help' => 'Slides the player back to the target tile.'],
        'ladder_bottom' => ['label' => 'Ladder bottom',  'icon' => 'ladder',    'card' => null,       'color' => '#16a34a', 'help' => 'Lifts the player up to the target tile.'],
    ],

    // Card icons that flavour a kind for one content mode (a tile's own icon always wins).
    'mode_icons' => [
        'drinks' => ['self' => 'martini', 'everyone' => 'cheers', 'pick' => 'wine'],
        'family' => ['self' => 'party', 'everyone' => 'balloons'],
    ],

    // What a Mystery spin can turn into (kind => weight).
    'spin_table' => ['self' => 3, 'pick' => 3, 'everyone' => 2, 'fact' => 2, 'bluff' => 2, 'spotlight' => 3, 'power' => 2, 'blank' => 1],

    // Card kinds the admin can write text for.
    'card_kinds' => [
        'self'     => 'You do it',
        'pick'     => 'Pick a friend',
        'everyone' => 'Everyone',
        'duel'     => 'Duel (loser does it)',
        'fact'      => 'True or false (statement + answer)',
        'bluff'     => 'Fact or bluff (prompt)',
        'spotlight' => 'Spotlight (creative prompt)',
        'rule'      => 'New rule',
    ],

    /*
    | Powers. Players start with `start` of each; Power-up tiles add more (cap `max`).
    */
    'powers' => [
        'shield' => ['label' => 'Shield', 'icon' => 'shield', 'start' => 1, 'max' => 3,
                     'help' => 'Block one hit aimed at you.'],
        'reroll' => ['label' => 'Reroll', 'icon' => 'reroll', 'start' => 1, 'max' => 3,
                     'help' => 'Throw your die again after seeing the number.'],
    ],

    /*
    | What a lost True-or-False / lost bluff / failed Spotlight costs. [mode][intensity][units] — a card's `penalty`
    | (1 or 2) is the number of units. Chill is gentler, Spicy bites harder. Family mode swaps drinks for forfeits.
    */
    'penalties' => [
        'drinks' => [
            1 => [1 => 'Take a sip.',  2 => 'Take two sips.'],
            2 => [1 => 'Take a shot.', 2 => 'Take two shots.'],
            3 => [1 => 'Take a shot.', 2 => 'Take two shots — no chaser.'],
        ],
        'family' => [
            1 => [1 => 'Do three star jumps.', 2 => 'Do six star jumps.'],
            2 => [1 => 'Do five star jumps.',  2 => 'Do ten star jumps.'],
            3 => [1 => 'Dance like nobody is watching for ten seconds.', 2 => 'Sing the chorus of any song — loudly.'],
        ],
    ],

    'players' => ['min' => 2, 'max' => 12],

    'timing' => [
        'turn_seconds'      => 40,  // online: idle turn auto-passes after this
        'pending_seconds'   => 25,  // online: an unanswered prompt resolves itself after this
        'missed_turn_limit' => 4,   // online: consecutive missed turns before a player is dropped
        'room_keep_days'    => 3,   // finished/idle rooms older than this are purged
        // Mirrors the client's animation durations so the next turn's timer doesn't
        // start burning while the previous move is still on screen.
        'hop_ms'            => 260,
        'pre_hop_ms'        => 580,
        'move_extra_ms'     => 1300,
        'landing_extra_ms'  => 500,
    ],

    'colors' => ['#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#a855f7', '#ec4899', '#14b8a6', '#f97316', '#84cc16', '#06b6d4', '#e11d48', '#6366f1'],

    // Player avatars: the retro pixel roster lives in resources/avatars.json (see App\Support\Avatars).

    // Reaction buttons (icon keys) — floated over a player's token for everyone to see.
    'reactions' => ['jester', 'fire', 'thumb', 'heart', 'skull', 'party', 'fist', 'sparkles'],

    // Board used when none is picked / the default art for the seeded starter board.
    'default_board_art' => '/img/boards/snl-night.webp',
];
