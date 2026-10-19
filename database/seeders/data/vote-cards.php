<?php

/*
| "Most likely to…" vote cards. Online rooms with 3+ players: everyone taps who it is on their own phone, the most-voted
| player takes the penalty for the room's mode (a tie shares it). mode => intensity (1 Chill · 2 Normal · 3 Spicy) => [lines].
| 'all' works in every mode. Keep them friendly: nothing cruel, nothing about bodies, nothing that targets one person for real.
*/
return [
    'all' => [
        1 => [
            'Who is most likely to become famous?',
            'Who is most likely to laugh at the wrong moment?',
            'Who is most likely to get lost with a map in their hand?',
            'Who is most likely to forget their own birthday?',
            'Who is most likely to be late to their own wedding?',
            'Who is most likely to win a dance contest?',
            'Who is most likely to adopt ten pets?',
            'Who is most likely to fall asleep first tonight?',
            'Who is most likely to cry at a movie?',
            'Who is most likely to say "I told you so"?',
        ],
        2 => [
            'Who is most likely to survive on a desert island?',
            'Who is most likely to start a business and make it big?',
            'Who is most likely to talk their way out of a speeding ticket?',
            'Who is most likely to arrive an hour late and blame traffic?',
            'Who is most likely to text the wrong person?',
            'Who is most likely to say yes to a spontaneous trip tonight?',
            'Who is most likely to be the first to cry at a wedding?',
            'Who is most likely to forget why they walked into a room?',
            'Who is most likely to keep a secret to the grave?',
            'Who is most likely to accidentally start a rumour?',
        ],
        3 => [
            'Who is most likely to get into an argument with a stranger online?',
            'Who is most likely to ghost someone and feel terrible later?',
            'Who is most likely to say something they regret at 2am?',
            'Who is most likely to cause a scene at a restaurant?',
            'Who is most likely to give the most dramatic apology?',
            'Who here has the best poker face, and who would crack first?',
        ],
    ],
    'drinks' => [
        1 => ['Who is most likely to say "just one more" and mean it?', 'Who is most likely to be the designated driver for the night?'],
        2 => ['Who is most likely to sing karaoke without being asked twice?', 'Who is most likely to give a very emotional speech after one drink?'],
        3 => ['Who is most likely to start a dance floor on their own?', 'Who is most likely to lose their phone tonight?'],
    ],
    'boyz' => [
        1 => ['Who is most likely to win an argument about football with zero facts?', 'Who is most likely to bring snacks to a game night?', 'Who is most likely to be the group\'s unofficial coach?'],
        2 => ['Who is most likely to say they are five minutes away but still be at home?', 'Who is most likely to skip the gym but post about it?', 'Who is most likely to give the worst advice with the most confidence?'],
        3 => ['Who is most likely to start a business and ask the group for money?', 'Who is most likely to take a sports bet way too seriously?', 'Who is most likely to cry when their team loses?'],
    ],
    'girlies' => [
        1 => ['Who is most likely to be ready first, and still make everyone wait?', 'Who is most likely to take a hundred photos for one good one?', 'Who is most likely to be the group\'s therapist?'],
        2 => ['Who is most likely to say "I\'m on my way" while still doing their hair?', 'Who is most likely to have the most unread messages?', 'Who is most likely to give the best compliment at the right time?'],
        3 => ['Who is most likely to start a group-chat drama?', 'Who is most likely to catch feelings in one weekend?', 'Who is most likely to keep a secret and still look innocent?'],
    ],
    'lovers' => [
        1 => ['Who is most likely to remember the anniversary date?', 'Who is most likely to cry at a wedding?', 'Who is most likely to plan the surprise?'],
        2 => ['Who is most likely to say "I love you" first in a relationship?', 'Who is most likely to forget a date night?', 'Who is most likely to fall in love at first sight?'],
        3 => ['Who is the best kisser? (the table decides)', 'Who is most likely to write a love letter?', 'Who is most likely to propose in a public place?'],
    ],
];
