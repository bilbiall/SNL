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
        'swap'          => ['label' => 'Swap places',    'icon' => 'swap',      'card' => 'swap',       'color' => '#8b5cf6', 'help' => 'The roller picks a player and they trade board positions.'],
        'fact'          => ['label' => 'True or false',  'icon' => 'fact',      'card' => 'fact',     'color' => '#06b6d4', 'help' => 'The game states a fact — the roller says true or false. Wrong answer = a penalty.'],
        'bluff'         => ['label' => 'Fact or bluff',  'icon' => 'jester',    'card' => 'bluff',    'color' => '#14b8a6', 'help' => 'The roller states a fact (true or made up). Anyone can call bluff — then it must be proven, or someone drinks.'],
        'spotlight'     => ['label' => 'Spotlight',      'icon' => 'mic',       'card' => 'spotlight','color' => '#fb923c', 'help' => 'A creative prompt on the clock — a movie for the moment, a song, a hot take. Fail it and take a penalty.'],
        'heart'         => ['label' => 'Love ladder',    'icon' => 'heart',     'card' => 'heart',    'color' => '#fb7185', 'help' => 'A sweet moment: pay a compliment, share a memory, toast someone. No penalty — just good vibes.'],
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
    // Chance (%) that a Pick-a-friend square lets the wheel choose the victim instead of the roller (needs cards containing {target}).
    'random_pick_chance' => 35,

    'spin_table' => ['self' => 3, 'pick' => 3, 'everyone' => 2, 'fact' => 2, 'bluff' => 2, 'spotlight' => 3, 'heart' => 1, 'power' => 2, 'blank' => 1],

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
        'heart'     => 'Love ladder (compliments & sweet moments)',
        'swap'      => 'Swap places (flavour line)',
        'neutral'   => 'Free square (flavour line — no action)',
        'quickfire' => 'Quickfire (10-second challenge on a free square)',
        'groupgame' => 'Group game (everyone plays, on a free square)',
        'gift'      => 'Free gift (flavour line when a Shield drops in)',
        'rollagain' => 'Roll again (flavour line)',
    ],

    /*
    | Free squares: calm by default, but some of them do something small and fun.
    | `free_event_chance` = % of free-square landings that trigger one; `free_events` = which, and how often (weights).
    | quickfire: a 10-second challenge · rollagain: another turn · gift: a free Shield · groupgame: everyone plays.
    */
    'free_event_chance' => 40,
    'free_events' => ['quickfire' => 4, 'rollagain' => 2, 'gift' => 2, 'groupgame' => 3],
    'event_icons' => ['quickfire' => 'clock', 'rollagain' => 'dice', 'gift' => 'sparkles', 'groupgame' => 'team'],
    'event_colors' => ['quickfire' => '#f97316', 'groupgame' => '#22c55e', 'gift' => '#eab308', 'rollagain' => '#38bdf8'],

    /*
    | Powers. Players start with `start` of each; Power-up tiles add more (cap `max`).
    */
    'powers' => [
        'shield' => ['label' => 'Shield', 'icon' => 'shield', 'start' => 1, 'max' => 5,
                     'help' => 'Block one hit aimed at you.'],
        // Reroll is switched off for now (the engine and its popups ignore it). Put this line back to bring it back:
        // 'reroll' => ['label' => 'Reroll', 'icon' => 'reroll', 'start' => 1, 'max' => 3, 'help' => 'Throw your die again after seeing the number.'],
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

    /*
    | Meme / reaction sounds (public/audio/reactions). The FILE NAME decides the moment — any word below in the name makes
    | the sound a candidate for that moment (word-start match, so "mischiev" catches "mischievous"). A sub-folder named
    | after a moment (reactions/snake/…) also works. Unmatched files play at random moments ("random").
    | `chance` = how often that moment plays a sound at all; `cooldown_ms` = the quiet gap between any two meme sounds.
    */
    'reaction_sounds' => [
        'cooldown_ms' => 2000,
        'volume' => 0.9,
        'chance' => ['pick' => 1.0, 'shot' => 0.85, 'fail' => 0.95, 'snake' => 1.0, 'ladder' => 1.0, 'correct' => 0.9, 'win' => 1.0, 'bluff' => 1.0, 'swap' => 0.9, 'duel' => 1.0, 'everyone' => 0.7, 'power' => 0.8, 'block' => 1.0, 'wasted' => 1.0, 'timeout' => 0.9, 'again' => 0.8, 'joined' => 0.9, 'rule' => 0.7, 'think' => 0.55, 'start' => 1.0, 'six' => 0.7, 'one' => 0.6, 'neutral' => 0.45, 'heart' => 0.8, 'close' => 0.9],
        'assign' => [
            'snake' => ['gta-san-andreas-ah-shit-here-we-go-again', 'sadtrombone.swf', 'universfield-comedic-fail-259683', 'fahhh_KcgAXfs', 'oh-shit-not-good_ZUcKH4I', 'man-screaming-aaaah', 'universfield-funny-cartoon-scream-229172', '01-the-screaming-sheep', 'alex_jauk-insane-funny-scream-199845', 'jesus-christ-kid', 'eh-eh-ehhhh', 'aki-ya-mungu-hii-ni-mambo-gani', 'downer_noise', 'titigwen-mayday-mayday-392997', 'you-are-a-mumu-man-sounds-effect-audiotrimmer', 'anderdingus'],
            'fail' => ['wrong_5', 'dissapointment-spongebob', 'downer_noise', 'universfield-comedic-fail-259683', 'sadtrombone.swf', 'oceaneyes91-no-gas-in-it-242768', 'stephen-a-smith-we-dont-care', 'africa-crying-laugh-commercial', 'indian-sorry', 'oh-shit-iam-sorry', 'bone-crack', 'ankle-breaker_j3Df0TU', 'jesus-christ-kid', 'fahhh_KcgAXfs', 'alex_jauk-sarcastic-clapping-sound-186117', 'they-ask-you-how-you-are-and-you-just-have-to-say-that-youre-fine-sound-effect_IgYM1CV'],
            'one' => ['sadtrombone.swf', 'wrong_5', 'fahhh_KcgAXfs', 'oceaneyes91-no-gas-in-it-242768', 'downer_noise', 'dissapointment-spongebob', 'jesus-christ-kid'],
            'ladder' => ['anime-wow-sound-effect', 'omg-wow', 'omgwow', 'siiiuuuuuu', 'ronaldo-suiiiiiiiiiiiii', 'roninthebro-isnt-that-amazing-meme-sfx-172488', 'universfield-my-good-120319', 'universfield-yeah-boy-114748', 'yeah-boymp4', 'travis-scott-weee-sound', 'yippeeeeeeeeeeeeee', 'yeet_ivPgINo', '6-_-7-watch-yo-jet-bro-446063'],
            'six' => ['siiiuuuuuu', 'ronaldo-suiiiiiiiiiiiii', 'anime-wow-sound-effect', 'universfield-yeah-boy-114748', 'yeah-boymp4', 'omgwow', 'roninthebro-isnt-that-amazing-meme-sfx-172488', 'michael-jackson-hee-hee', 'another-one_dPvHt2Z'],
            'correct' => ['okay-meme', 'okayy-okayyy', 'ive-got-this', 'city-boy', 'cute-arigato', 'nouveau-jingle-netflix', 'windows-xp-startup', 'sensational-future', 'rizz-sound-effect', 'ehee', 'universfield-yeah-boy-114748', 'yeah-boymp4', 'good-shot-bhaiya', 'michael-jackson-hee-hee', 'children-yaysound-effect', 'omg-wow', 'roninthebro-isnt-that-amazing-meme-sfx-172488'],
            'win' => ['and-his-name-is-john-cena', 'celebrate_6Ly1unY', 'crowd-applause-cheer-small-group_tUQjCQt', 'claps-with-cheers-audience', 'ronaldo-suiiiiiiiiiiiii', 'happy-music', 'heavenly-music-gaming-sound-effect-hd-mp3cut', 'children-yaysound-effect', 'meme-de-creditos-finales', 'windows-xp-startup', 'nouveau-jingle-netflix', 'ankara-messi-best-sound'],
            'wasted' => ['grand-theft-auto-v-wasted', 'gta-v-death-sound-effect-102'],
            'timeout' => ['a-few-minutes-later', 'a-few-moments-later-sponge-bob-sfx-fun', 'crickets.swf', 'crickets-chirping', 'wake-up-meme-close-up-aetrim1609692048785-aemerge1609692158800', 'please-speed-i-need-this'],
            'neutral' => ['crickets.swf', 'crickets-chirping', 'a-few-minutes-later', 'a-few-moments-later-sponge-bob-sfx-fun', 'chill-guy_2Yzh6jq', 'they-ask-you-how-you-are-and-you-just-have-to-say-that-youre-fine-sound-effect_IgYM1CV', 'he-needs-some-milk-vine', 'stephen-a-smith-we-dont-care', 'okay-meme', 'wdym-druski_aLb98G4', 'ustoopid', 'goofy-laughh', 'cartoonlaugh1'],
            'heart' => ['romanceeeeeeeeeeeeee', 'heyy-daddyyyyy-omg', 'freesound_community-pinkie-pie-ooh-86125', 'studio-audience-awwww-sound-fx', 'cute-arigato', 'rizz-sound-effect', 'rizzbot-laugh', 'she-want-me-sollumininati-meme', 'freesound_community-ooh-aha-47811'],
            'pick' => ['universfield-mischievous-laugh-140131', 'wait-a-minute-who-are-you', 'u_ecyjl18pfk-wait-a-minute-who-are-you-425166', 'finish-him', 'get-yo-fat-ass-back-here', 'taken-i-will-find-you-and-i-will-kill-you-hd', 'tf_nemesis', 'spidermans-laugh', 'risada-coringa-joaquin', 'look-at-this-dude', 'dun-dun-dun-sound-effect-brass_8nFBccR', 'bombastic-side-eye', 'where-are-you-goingg', 'heknew', 'scout-tf2-wananana', 'i-dont-want-peace', 'fbi-open-up_dwLhIFf', 'rizzbot-laugh', 'goofy-laughh', 'cartoonlaugh1', 'laugh-3_9wVKqU7', 'alex_jauk-strange-incomprehensible-call-203142', 'freesound_community-ooh-aha-47811'],
            'shot' => ['good-shot-bhaiya', 'gunshotjbudden', 'another-one_dPvHt2Z', 'air-horn-troll-enter', 'travis-scott-weee-sound', 'ive-got-this', 'say-wallahi-bro_egHVr2Y', 'gah-dayum', 'friday-damn', 'michael-jackson-hee-hee', 'titigwen-mayday-mayday-392997', 'alex_jauk-insane-funny-scream-199845', 'woo-wat-da-heel', 'yippeeeeeeeeeeeeee', 'he-needs-some-milk-vine', 'oh-shit-iam-sorry', 'ustoopid', 'punch-gaming-sound-effect-hd_RzlG1GE'],
            'bluff' => ['stop-the-cap-cut', 'bombastic-side-eye', 'wdym-druski_aLb98G4', 'sike-1', 'heknew', 'are-you-out-of-your-mind-greenscreen-change-quality-and-end-wont-cut-off_2', 'what-is-he-saying', 'why-are-you-geh', 'why-are', 'no-no-wait-wait', 'alex_jauk-strange-incomprehensible-call-203142', 'alex_jauk-sarcastic-clapping-sound-186117', 'dun-dun-dun-sound-effect-brass_8nFBccR', 'wait-a-minute-who-are-you', 'u_ecyjl18pfk-wait-a-minute-who-are-you-425166', 'ehee'],
            'swap' => ['the-bluetooth-device-is-connecteda-successfully', 'scout-tf2-wananana', 'michael-jackson-hee-hee', 'where-are-you-goingg', 'yeet_ivPgINo', 'ehee', 'no-no-wait-wait', 'wait-a-minute-who-are-you', 'sensational-future', 'travis-scott-weee-sound'],
            'duel' => ['tf_nemesis', 'sword_slash-ab22fe02-3826-345a-80ad-dc22261a9127', 'punch-gaming-sound-effect-hd_RzlG1GE', 'ankle-breaker_j3Df0TU', 'bone-crack', 'finish-him', 'gunshotjbudden', 'i-dont-want-peace', 'scout-tf2-wananana', 'tuco-get-out', 'fbi-open-up_dwLhIFf', 'titigwen-mayday-mayday-392997', 'taken-i-will-find-you-and-i-will-kill-you-hd', 'dun-dun-dun-sound-effect-brass_8nFBccR', 'air-horn-troll-enter'],
            'everyone' => ['air-horn-troll-enter', 'yippeeeeeeeeeeeeee', 'funke-funke', '1994-nokia-ringtone', 'city-boy', 'claps-with-cheers-audience', 'children-yaysound-effect', 'travis-scott-weee-sound', 'studio-audience-awwww-sound-fx', 'indian-song', 'freesound_community-ooh-aha-47811', 'anime-wow-sound-effect', 'siiiuuuuuu'],
            'power' => ['nouveau-jingle-netflix', 'windows-xp-startup', 'sensational-future', 'rizz-sound-effect', 'universfield-my-good-120319', 'ive-got-this', 'cute-arigato', 'anime-wow-sound-effect', 'okay-meme', 'another-one_dPvHt2Z', 'omgwow'],
            'block' => ['denied', 'oceaneyes91-no-gas-in-it-242768', 'sike-1', 'leave-me-alone-ahh', 'tuco-get-out', 'ive-got-this', 'stop-the-cap-cut', 'stephen-a-smith-we-dont-care', 'ustoopid'],
            'start' => ['windows-xp-startup', 'air-horn-troll-enter', 'nouveau-jingle-netflix', 'yippeeeeeeeeeeeeee', 'travis-scott-weee-sound', 'funke-funke', 'city-boy', '1994-nokia-ringtone', 'anime-wow-sound-effect', 'woo-wat-da-heel'],
            'close' => ['dun-dun-dun-sound-effect-brass_8nFBccR', 'freesound_community-ooh-aha-47811', 'omg-wow', 'no-no-wait-wait', 'titigwen-mayday-mayday-392997', 'heknew', 'sensational-future', 'wait-a-minute-who-are-you', 'gah-dayum', 'omgwow'],
            'again' => ['another-one_dPvHt2Z', 'gta-san-andreas-ah-shit-here-we-go-again', 'ehee', 'yeet_ivPgINo', 'okayy-okayyy', 'travis-scott-weee-sound'],
            'joined' => ['the-bluetooth-device-is-connecteda-successfully', 'windows-xp-startup', 'look-at-this-dude', 'city-boy', 'air-horn-troll-enter', 'nouveau-jingle-netflix', 'ehee', 'cute-arigato'],
            'rule' => ['let-me-tell-you-something', 'dun-dun-dun-sound-effect-brass_8nFBccR', 'they-ask-you-how-you-are-and-you-just-have-to-say-that-youre-fine-sound-effect_IgYM1CV', 'i-dont-want-peace', 'bombastic-side-eye', 'fbi-open-up_dwLhIFf', 'stephen-a-smith-we-dont-care'],
            'think' => ['oh-no-no-no-tik-tok-song-sound-effect', 'the-saxophones-getting-louder', 'universfield-comedy-flute-144564', 'sad-meow-song', 'english-or-spanish-song', 'let-me-know', 'spiderman-meme-song', 'dexter-meme', 'spongebob-sad-song', 'let-her-go', 'wait-wait-wait-what-the-hell-legend-sound', 'hahahaha_7', 'funny-sound-that-will-make-you-to-laugh_1', 'indian-song', 'meme-de-creditos-finales', 'cat-iphone-ringtone', 'go-crazy-go-stupid-aughgh_jyrUNr3', 'please-speed-i-need-this', 'kwebbelkop-laughing-or-smth'],
        ],
        'think_volume' => 0.55,
        'categories' => [
            'pick'     => ['mischiev', 'evil', 'sneaky', 'sinister', 'gotcha', 'laugh', 'ooh', 'aha', 'uh oh', 'wait a minute', 'who are you', 'strange'],
            'shot'     => ['scream', 'mayday', 'cheers', 'drink', 'shot', 'insane', 'laugh', 'goofy', 'ooh', 'burp', 'cough'],
            'fail'     => ['fail', 'clap', 'sarcas', 'no gas', 'flute', 'wrong', 'boo', 'sad', 'trombone', 'awkward', 'goofy', 'cricket', 'nope', 'bruh', 'oof'],
            'snake'    => ['scream', 'mayday', 'fail', 'no gas', 'cartoon', 'insane', 'flute', 'sad', 'fall', 'crash', 'bruh', 'oof', 'nooo'],
            'ladder'   => ['wow', 'amazing', 'yeah', 'good', 'whoa', 'yay', 'nice', 'jet', 'fly', 'up', 'woo'],
            'correct'  => ['yeah', 'good', 'amazing', 'wow', 'aha', 'nice', 'correct', 'right', 'ding', 'woo', 'yay'],
            'win'      => ['yeah', 'amazing', 'wow', 'good', 'jeff', 'jet', 'win', 'victory', 'champion', 'woo', 'airhorn', 'fanfare'],
            'bluff'    => ['wait', 'who are', 'strange', 'incomprehensible', 'call', 'jeff', 'ooh', 'sarcas', 'clap', 'sus', 'suspicious', 'hmm', 'really', 'liar'],
            'swap'     => ['wait', 'who are', 'jeff', 'strange', 'jet', 'flute', 'switch', 'swap', 'wee'],
            'duel'     => ['mayday', 'scream', 'jeff', 'jet', 'insane', 'cartoon', 'fight', 'battle', 'ready', 'duel', 'sword'],
            'everyone' => ['ooh', 'aha', 'wow', 'jeff', 'flute', 'jet', 'cheer', 'party', 'everybody', 'crowd'],
            'power'    => ['good', 'yeah', 'wow', 'amazing', 'jet', 'power', 'level up', 'boost', 'ding'],
            'start'    => ['party', 'start', 'ready', 'airhorn', 'yeah', 'cheer', 'lets go', 'woo', 'fanfare'],
            'six'      => ['yeah', 'wow', 'woo', 'good', 'amazing', 'jet', 'nice', 'sheesh', 'six'],
            'one'      => ['fail', 'sad', 'oof', 'bruh', 'trombone', 'flute', 'no gas', 'nope', 'cricket', 'awkward'],
            'neutral'  => ['cricket', 'awkward', 'hmm', 'wait', 'flute', 'sus', 'really', 'strange'],
            'heart'    => ['ooh', 'aww', 'love', 'kiss', 'pinkie', 'aha', 'wow', 'cute'],
            'close'    => ['wow', 'jet', 'drum', 'suspense', 'amazing', 'ooh', 'wait', 'mayday'],
            'wasted'   => ['wasted', 'death', 'died', 'dead', 'rip', 'game over', 'you need to leave', 'get out', 'bye', 'goodbye'],
            'timeout'  => ['later', 'crickets', 'sleep', 'wake up', 'waiting', 'slow', 'hurry', 'speed'],
            'again'    => ['again', 'another one', 'one more', 'encore'],
            'joined'   => ['connected', 'welcome', 'join', 'hello', 'hey', 'enter'],
            'rule'     => ['rule', 'listen', 'tell you something', 'attention', 'announcement', 'law'],
            'think'    => ['thinking', 'suspense', 'jeopardy', 'waiting', 'elevator', 'tension', 'saxophone', 'song', 'music'],
            'block'    => ['no gas', 'wait', 'jeff', 'good', 'clap', 'nope', 'denied', 'block', 'shield', 'not today'],
        ],
    ],

    /*
    | How much each level asks of the table (percent chances, per landing):
    |   skip_hit  — Chill only: a You-do-it / Pick-a-friend / Everyone card is waved through ("you're off the hook"), so the game is lighter.
    |   free_shot — Spicy: even a free square can bite (a shot in Drinks mode, a forfeit in Family mode). Normal stays as written.
    */
    'intensity_tuning' => [
        1 => ['skip_hit' => 28, 'free_shot' => 0],
        2 => ['skip_hit' => 0,  'free_shot' => 0],
        3 => ['skip_hit' => 0,  'free_shot' => 30],
    ],
    'spice_intros' => [
        'Spicy square — nothing is free tonight.', 'Even empty squares bite on Spicy.', 'No such thing as a safe square when it\'s Spicy.',
        'The board is feeling mean.', 'Surprise! This free square has a catch.', 'Spicy rule: free squares are a myth.', 'Plot twist — the quiet square bites.',
    ],
    'chill_passes' => [
        'Chill vibes — you\'re off the hook this time.', 'The board takes it easy on you.', 'A gentle moment — nothing to drink here.',
        'Chill mode: you get a pass.', 'Lucky you — the card looks away.', 'Easy does it — no penalty this round.',
    ],

    // The live site (used by the Android app to look for a newer APK). Set SNL_PUBLIC_URL in .env if it changes.
    'public_url' => env('SNL_PUBLIC_URL', 'https://snl.moski.org'),

    // Public room names are shown to strangers — these can't appear in them (whole words, case-insensitive).
    'blocked_words' => ['fuck', 'shit', 'cunt', 'bitch', 'nigger', 'nigga', 'faggot', 'whore', 'slut', 'rape', 'nazi', 'porn', 'dick', 'cock', 'pussy'],

    /*
    | The Buzz Meter (Drinks mode): every hit a player takes (a sip, a shot, a forfeit) fills it — shots count by their size.
    | `max` is the number that fills the bar; `levels` are [from, label]. The leaderboard on the left is ordered by it.
    */
    'buzz' => [
        'max' => 12,
        'drinks' => ['name' => 'Buzz Meter', 'levels' => [[0, 'Sober'], [2, 'Tipsy'], [4, 'Buzzed'], [7, 'Wobbly'], [10, 'Legend']]],
        // Family games have no meter: the left list is a plain leaderboard, furthest square first.
        'family' => ['name' => 'Leaderboard', 'enabled' => false, 'levels' => [[0, 'Calm']]],
    ],

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
