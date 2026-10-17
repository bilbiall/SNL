<?php

/*
| Clearer, more relatable cards. Run with:  php artisan db:seed --class=ClearerCardsSeeder --force
|   replace — cards whose wording was vague or confusing: [old text => new text]   (matched exactly, so edited cards are left alone)
|   cards   — new "Sip / Take a shot if you have ever …" cards, one everyday situation each: [intensity, text]
| Every line is something most people in the room will recognise straight away — no explaining needed.
*/
return [
    'replace' => [
        'Take a shot if you have called food for one "meal prep".' => 'Take a shot if you have ever bought snacks "for the week" and finished them in one day.',
        "Take a shot if you've got a talent you'd never show on a first date." => 'Take a shot if you have a weird habit you hide when someone new is around.',
        'Take a shot if you have ever gone back for your phone mid-date.' => 'Take a shot if you have ever checked your phone under the table during a date.',
        'Pick who would survive a reality-TV villain edit — they take a shot and a villain pose.' => 'Pick who would be the most dramatic housemate on a reality show — they take a shot and a dramatic pose.',
        'Pick who would last longest in a relationship with a stranger — they take a shot.' => 'Pick who would say yes to a blind date the fastest — they take a shot.',
        "Take a shot if you've sent a text and then thrown your phone." => "Take a shot if you've sent a text and then wished you could delete it from their phone.",
    ],

    'cards' => [
        // ── sip (easy) ──
        [1, 'Sip if you have ever opened the fridge three times hoping new food appeared.'],
        [1, 'Sip if you have ever walked into a room and forgotten why.'],
        [1, 'Sip if you have ever said "I will study tomorrow" and then did not study.'],
        [1, 'Sip if you have watched "just one more video" and lost a whole hour.'],
        [1, 'Sip if you have ever pretended to be on a call to avoid someone.'],
        [1, 'Sip if you have ever sent a voice note longer than three minutes.'],
        [1, 'Sip if you have ever skipped breakfast and regretted it by 10am.'],
        [1, 'Sip if you have ever eaten the last piece of something and hidden the evidence.'],
        [1, 'Sip if you have ever said "I am almost there" while still in the shower.'],
        [1, 'Sip if you have ever sung along to a song with completely the wrong words.'],
        [1, 'Sip if you have ever fallen asleep with your phone on your face.'],
        [1, 'Sip if you have ever stayed on a call just waiting for the other person to hang up first.'],

        // ── shot (medium) ──
        [2, 'Take a shot if you have ever sent money on M-Pesa to the wrong number.'],
        [2, 'Take a shot if you have ever borrowed a charger and never given it back.'],
        [2, 'Take a shot if you have ever told someone your phone was dead to dodge a call.'],
        [2, 'Take a shot if you have ever fallen asleep on the bus and missed your stop.'],
        [2, 'Take a shot if you have ever called a teacher or a boss "mum" or "dad" by mistake.'],
        [2, 'Take a shot if you have ever cried over a football match.'],
        [2, 'Take a shot if you have ever replied "okay" when you were actually upset.'],
        [2, 'Take a shot if you have ever cancelled plans on the same day you made them.'],
        [2, 'Take a shot if you have ever screenshotted a chat to show a friend what someone said.'],
        [2, 'Take a shot if you have ever been caught talking about someone the moment they walked in.'],
        [2, 'Take a shot if you have ever laughed at the wrong moment in a serious situation.'],
        [2, 'Take a shot if you have ever copied someone\'s homework or work and changed a few words.'],

        // ── shot (spicy) ──
        [3, 'Take a shot if you have ever saved someone\'s number under a fake name.'],
        [3, 'Take a shot if you have ever checked who viewed your WhatsApp status first thing in the morning.'],
        [3, 'Take a shot if you have ever read a message and waited on purpose before replying.'],
        [3, 'Take a shot if you have ever told a friend a secret you were asked not to share.'],
        [3, 'Take a shot if you have ever said "I am fine" and you were not fine.'],
        [3, 'Take a shot if you have ever blocked and then unblocked the same person in one week.'],
        [3, 'Take a shot if you have ever dated two people at the same time.'],
        [3, 'Take a shot if you have ever stayed in a relationship just because you were too lazy to end it.'],
    ],
];
