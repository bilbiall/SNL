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
        'spicy'  => ['label' => 'Spicy',  'icon' => 'fire',     'adult' => true,  'enabled' => false,
                     'blurb' => 'For couples. Coming soon.'],
    ],

    'intensities' => [
        1 => ['label' => 'Chill',  'icon' => 'thumb'],
        2 => ['label' => 'Normal', 'icon' => 'fire'],
        3 => ['label' => 'Wild',   'icon' => 'skull'],
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
        'fact'          => ['label' => 'Fun fact',       'icon' => 'fact',      'card' => 'fact',     'color' => '#06b6d4', 'help' => 'A random fact is read out to the table.'],
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
    'spin_table' => ['self' => 3, 'pick' => 3, 'everyone' => 2, 'fact' => 2, 'power' => 2, 'blank' => 1],

    // Card kinds the admin can write text for.
    'card_kinds' => [
        'self'     => 'You do it',
        'pick'     => 'Pick a friend',
        'everyone' => 'Everyone',
        'duel'     => 'Duel (loser does it)',
        'fact'     => 'Fun fact',
        'rule'     => 'New rule',
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

    // Player avatars (icon keys).
    'avatars' => ['av-tiger', 'av-wolf', 'av-frog', 'av-lion', 'av-panda', 'av-octopus', 'av-unicorn', 'av-monkey', 'av-penguin', 'av-owl', 'av-dragon', 'av-rabbit'],

    // Reaction buttons (icon keys) — floated over a player's token for everyone to see.
    'reactions' => ['jester', 'fire', 'thumb', 'heart', 'skull', 'party', 'fist', 'sparkles'],

    // Board used when none is picked / the default art for the seeded starter board.
    'default_board_art' => '/img/game/arcade/pesatrail.webp',
];
