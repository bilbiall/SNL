<?php

/*
| Free-square events: some "free" squares now do something small and fun (never all of them — calm squares stay calm).
|   quickfire  [intensity, prompt, seconds]  a 10-second challenge for the roller (fail = penalty, like Spotlight)
|   cards      kind => mode => intensity => lines
|                groupgame — everyone plays (honour system, the text says who pays)
|                gift      — flavour line shown when a free Shield drops in
|                rollagain — flavour line shown when the roller gets another turn
*/
return [
    'quickfire' => [
        [1, 'Name five fruits.', 10],
        [1, 'Name five animals that live in water.', 10],
        [1, 'Name four things you can find in a kitchen.', 8],
        [1, 'Say the days of the week backwards.', 10],
        [1, 'Name five countries in Africa.', 10],
        [1, 'Name three songs with a colour in the title.', 10],
        [1, 'Name five things that are round.', 8],
        [1, 'Name four footballers — or four athletes.', 8],
        [1, 'Count to ten in any language that is not your own.', 10],
        [1, 'Name five things you can wear on your head.', 8],
        [1, 'Name four cartoon characters.', 8],
        [1, 'Spell your full name backwards.', 12],
        [2, 'Name six brands of anything, no repeats.', 10],
        [2, 'Name five movies with a number in the title.', 10],
        [2, 'Name five artists who have had a number-one song.', 10],
        [2, 'Name six things you would find in a bar.', 8],
        [2, 'Name five cities beginning with the same letter.', 10],
        [2, 'Name four cocktails without hesitating.', 8],
        [2, 'Say "she sells sea shells" three times fast.', 8],
        [2, 'Name five things that are red — and one that is not a fruit.', 10],
        [2, 'List the planets in order from the sun.', 12],
        [2, 'Name five apps on your phone without looking.', 8],
        [2, 'Name five words that rhyme with "night".', 10],
        [2, 'Name four dances, then do one move of your favourite.', 12],
        [3, 'Name seven things you can do with a bottle cap.', 10],
        [3, 'Name five dating-app red flags.', 10],
        [3, 'Name six famous couples — real or fictional.', 10],
        [3, 'Name five things you should never text at 2 a.m.', 10],
        [3, 'Say "red lorry, yellow lorry" five times fast.', 8],
        [3, 'Name five songs that mention a drink.', 10],
        [3, 'Name six words that start with "un" and describe a bad night.', 10],
        [3, 'Name five villains from movies in under ten seconds.', 10],
        [3, 'Name four things you are secretly good at.', 8],
        [3, 'Name five people in this room and one compliment each. Go!', 12],
    ],

    'cards' => [
        'groupgame' => [
            'all' => [
                1 => [
                    'Category time: {topic}! Go round the table, one answer each — whoever stalls or repeats pays the penalty.',
                    'Thumb master! The roller secretly puts their thumb on the table. Everyone copies — last to notice pays.',
                    'Everyone points at who they think will win this game. Most-pointed-at takes a bow.',
                    'Rock-paper-scissors shoot-out: the roller plays everyone at once — beat them all or pay the penalty.',
                    'Never have I ever… everybody takes a turn; the roller goes first. Anyone who has done it cheers.',
                    'Staring contest! The roller picks the person on their left — first to blink pays.',
                    'Everyone names their favourite thing about the person on their right.',
                    'Rhyme time: the roller says a word, everyone must rhyme with it in turn. First to fail pays.',
                ],
                2 => [
                    'Categories: {genre} movies! Go round — no repeats, no pauses. Whoever breaks the chain pays.',
                    'Everyone shows their last photo in the camera roll. Funniest one (the table votes) gets the roller a free pass.',
                    'Most likely to… The roller reads three, everyone points. Most-pointed-at pays.',
                    'Song chain: the roller sings a line, the next person continues with a song starting on the last word.',
                    'Silent countdown! Everyone counts to 10 one at a time without planning order. Two people speak at once — they pay.',
                    'Emoji charades: the roller acts a movie with no words. First to guess picks who pays.',
                    'Heads up: everyone closes their eyes, the roller points at someone — that person owns up with a wave.',
                    'Two truths and a lie from the roller — the table votes. Fooled most? They pay.',
                ],
                3 => [
                    'Hot take round: everyone gives a hot take on {hot_topic}. The table cheers loudest for the winner; the quietest pays.',
                    'Confession round: everyone shares a harmless secret — or pays the penalty.',
                    'Everyone swaps seats clockwise, then names who they would swap lives with.',
                    'Red flag or green flag? The roller reads three, the table votes. Wrong majority pays.',
                    'Whisper challenge: the roller whispers a phrase to the left, it travels round. If it arrives wrong, the one who broke it pays.',
                    'Everyone rates the roller\'s outfit out of ten. The lowest score pays the penalty — the roller picks which.',
                ],
            ],
            'drinks' => [
                1 => ['Waterfall (light): everyone sips together until the roller stops — then stops one by one clockwise.', 'Cheers round! Everyone clinks with everyone — last clink pays a sip.'],
                2 => ['Waterfall! Everyone drinks until the roller stops; the person to their left stops next, and so on.', 'Social: everyone with their phone in hand takes a sip.'],
                3 => ['Categories: cocktails! Go round — first to stall takes a shot.', 'Bartender\'s choice: the roller picks the toast, everybody drinks to it.'],
            ],
            'family' => [
                1 => ['Everyone does the same silly dance move the roller invents — last to copy wins the cheer.', 'Everyone shares their favourite snack in one word.'],
                2 => ['Animal sounds: the roller picks an animal, everyone makes the sound at once. Funniest wins.', 'Everyone strikes a pose that sums up how they feel right now.'],
                3 => ['Freeze! Everyone holds a pose until the roller counts to ten. First mover owes a joke.', 'Whoever laughs first in a ten-second silence owes everyone a compliment.'],
            ],
        ],
        'gift' => [
            'all' => [
                1 => ['Lucky find! A shield was hiding under this square.', 'Surprise! Someone left you a present.', 'You spot something shiny — a free power-up!', 'The board likes you today — here is a gift.'],
                2 => ['Pocket check — you found a shield!', 'A mysterious stranger slides you a gift and winks.', 'Finders keepers: a free power-up.'],
                3 => ['The game slips you a favour — don\'t tell the others.', 'A little bribe from the house: a free power-up.'],
            ],
        ],
        'rollagain' => [
            'all' => [
                1 => ['Lucky break — roll again!', 'The dice want another go. Roll again!', 'Bonus turn! Go on, roll again.', 'Free spin on the dice — roll again!'],
                2 => ['Double trouble! Roll again.', 'Tailwind! You get another roll.', 'The universe says: again!'],
                3 => ['Chaos approved — roll again!', 'Fortune favours the bold: another roll.'],
            ],
        ],
    ],
];
