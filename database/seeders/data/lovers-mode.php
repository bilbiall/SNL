<?php

/*
| Content for the "lovers" mode: couples (or a double date) getting to know each other better. Questions, memories, guesses,
| dreams and sweet forfeits, getting a little deeper at each intensity (1 Chill · 2 Normal · 3 Spicy). Flirty, never explicit,
| and everything is optional — skip anything you are not comfortable with. Forfeits live in 'penalties' in config/snl.php.
| Same shape as friends-modes.php. "your partner" = the person you are playing with; {other} = a player picked by the game.
*/
return [

    'cards' => [

        // ───────── You do it ─────────
        'self' => [
            'lovers' => [
                1 => [
                    'Tell your partner the moment you realised you liked them.',
                    'What was your first impression of your partner? Be honest!',
                    'Share the song that reminds you of your partner, and why.',
                    'What is the first thing you noticed about your partner?',
                    'Describe your perfect lazy day together.',
                    'What is one small thing your partner does that always makes you smile?',
                    'Name your partner\'s favourite food, colour and film. No peeking!',
                    'What nickname would you give your partner if you had to pick a new one?',
                    'Tell your partner what you were thinking on your first date.',
                    'Say what you love most about how your partner laughs.',
                ],
                2 => [
                    'What is something about your partner that surprised you after you got together?',
                    'Describe a moment you felt really proud of your partner.',
                    'What is one thing you have never told your partner but would like to?',
                    'What is your favourite memory of the two of you? Tell it like a story.',
                    'What does your partner do that makes you feel most loved?',
                    'If you could relive one day with your partner, which would it be?',
                    'What is something new you would like to try together this year?',
                    'Tell your partner what you admire about them that they might not know.',
                    'What is one habit of yours that you know your partner puts up with? Thank them for it.',
                    'Where do you see the two of you in five years? Paint the picture.',
                ],
                3 => [
                    'Tell your partner something that made you fall for them, and you have never said out loud.',
                    'What is one fear you have about the future that you would like your partner to know?',
                    'Describe the moment you knew this was serious.',
                    'What is something you want your partner to promise you?',
                    'What is the bravest thing your partner has done for you?',
                    'What is the one thing you wish you could change about how you handled an argument?',
                    'Tell your partner what you would write on their love letter right now. Say it out loud.',
                    'Share a dream you have never shared with anyone, and ask your partner to be part of it.',
                    'What does a perfect romantic evening look like for you, minute by minute?',
                    'Describe what home means to you, and what part of it is your partner.',
                ],
            ],
        ],

        // ───────── Pick a friend (roller chooses) ─────────
        'pick' => [
            'lovers' => [
                1 => [
                    'Pick who has the best taste in music. They name the song of their life.',
                    'Pick who is the better cook. They share their signature dish.',
                    'Pick who would be the best travel buddy. They name a dream destination.',
                    'Pick who is the funniest. They tell their favourite joke.',
                    'Pick who is more romantic. They share their idea of a perfect date.',
                    'Pick who gets ready faster. They explain their secret.',
                ],
                2 => [
                    'Pick who is more likely to cry at a film. They name the film that got them.',
                    'Pick who is the better listener. They say what makes a good listener.',
                    'Pick who is more adventurous. They describe the wildest thing they would try.',
                    'Pick who is more likely to forget an anniversary. They explain how they will make up for it.',
                    'Pick who is the bigger dreamer. They share one big dream.',
                    'Pick who plans the dates. They describe their dream date night.',
                ],
                3 => [
                    'Pick who is the better kisser, by vote of the table. They give a three-word review of their own technique.',
                    'Pick who is more jealous (a little). They say what makes them feel secure.',
                    'Pick who falls in love more easily. They tell the story of their first love.',
                    'Pick who is more likely to say sorry first. They share what makes apologising hard.',
                    'Pick who would be the better parent. They describe the parent they hope to be.',
                    'Pick who says "I love you" first. They say it in the most dramatic voice they can.',
                ],
            ],
        ],

        // ───────── Everyone ─────────
        'everyone' => [
            'lovers' => [
                1 => [
                    'Everyone says their partner\'s favourite meal on three. Right answers score a point; wrong answers get teased.',
                    'Everyone names the colour their partner wears most often.',
                    'Everyone gives their partner a high five and a compliment.',
                    'Everyone says the first word that comes to mind to describe their partner.',
                    'Everyone shares the song they would pick for their first dance.',
                ],
                2 => [
                    'Everyone says where they first met their partner, and what they were wearing. Compare notes!',
                    'Everyone names their partner\'s biggest dream. Partners confirm it.',
                    'Everyone says their partner\'s love language: words, time, gifts, touch or acts of service.',
                    'Everyone tells what they would cook for their partner on a perfect evening.',
                    'Everyone says one thing they love about the person on their right.',
                ],
                3 => [
                    'Everyone says the nicest thing their partner has ever said to them.',
                    'Everyone tells their partner one thing they want to do more of together.',
                    'Everyone says what they were most nervous about on their first date.',
                    'Everyone shares their partner\'s best quality, then their partner\'s worst habit, with love.',
                    'Everyone says the moment they felt closest to their partner this year.',
                ],
            ],
        ],

        // ───────── Duel (loser pays) ─────────
        'duel' => [
            'lovers' => [
                1 => ['The loser tells the winner one thing they love about them.', 'The loser gives the winner a compliment in a silly voice.'],
                2 => ['The loser gives the winner a 20-second shoulder rub.', 'The loser tells the winner their favourite memory with them.', 'The loser sings a line of a love song to the winner.'],
                3 => ['The loser has to slow-dance with the winner for 30 seconds.', 'The loser tells the winner something they have never said before.'],
            ],
        ],

        // ───────── New rule ─────────
        'rule' => [
            'lovers' => [
                1 => ['New rule: before every turn, give your partner a compliment.', 'New rule: call your partner "my love" until the next rule.', 'New rule: no phones. Slip up, give your partner a hug.'],
                2 => ['New rule: hold hands at all times. Let go, and you owe a compliment.', 'New rule: every sentence ends with "darling". Slip up, give a compliment.', 'New rule: whisper everything you say to your partner.'],
                3 => ['New rule: before each answer, look your partner in the eyes for five seconds.', 'New rule: say "I love you" in a different language every turn.', 'New rule: finish each other\'s sentences. Slip up, tell your partner something sweet.'],
            ],
        ],

        // ───────── Kind words ─────────
        'heart' => [
            'lovers' => [
                1 => ['Tell {other} something you appreciate about them.', 'Give {other} a compliment about something they do well.', 'Tell {other} what makes them easy to be around.'],
                2 => ['Tell {other} about a time they made you feel special.', 'Say what you hope for {other} in the year ahead.', 'Share a favourite memory you have of {other}.'],
                3 => ['Tell {other} one thing you admire about their relationship.', 'Say what you have learned about love from watching {other}.', 'Give {other} a short, heartfelt toast.'],
            ],
        ],

        // ───────── Group games ─────────
        'groupgame' => [
            'lovers' => [
                1 => ['Guess who: everyone writes their partner\'s favourite food in their head. On three, say it. Wrong answers pay.', 'Everybody names a love song, round the table. First to stall pays.', 'Everybody names a romantic movie, round the table. No repeats, first to stall pays.'],
                2 => ['Two truths and a lie: each person tells three things about themselves. Their partner guesses the lie. Wrong guesses pay.', 'Everybody names a famous couple, round the table. First to stall pays.', 'The roller gives a letter. Everyone names a date-night idea starting with it. First to stall pays.'],
                3 => ['Partner quiz: each person asks their partner one question about themselves. Wrong answer pays.', 'Everybody finishes the sentence: "My partner is at their best when..." The roller picks the sweetest answer.'],
            ],
        ],
    ],

    // The wheel's deck: {target} is the player the wheel picked.
    'random_pick' => [
        'lovers' => [
            1 => ['The wheel picked {target}! Tell your partner what you love most about them.', '{target} was chosen: describe your partner in three words.', 'The wheel chose {target}: share the song that reminds you of your partner.'],
            2 => ['{target}, the wheel says: tell the story of your first kiss with your partner, or your first hug.', 'Fate chose {target}: say one thing you have always wanted to do together.', 'The wheel picked {target}! Share a memory your partner would never expect you to remember.'],
            3 => ['The wheel is bold: {target}, tell your partner what you want them to know before the night ends.', '{target} was chosen: say what you were thinking the first time you said "I love you".', 'Fate chose {target}: tell your partner one thing you promise to do for them this year.'],
        ],
    ],

    // ───────── True or false ─────────
    'facts' => [
        'lovers' => [
            [1, 'Sea otters hold hands while they sleep so they do not drift apart.', true, 'Otters rest in groups called rafts and often link paws to stay together.', 1],
            [1, 'All penguins mate for life.', false, 'Many penguin species pair up for a season and then choose again. Some stay together longer.', 1],
            [1, 'The heart emoji shape looks like a real human heart.', false, 'A real heart is a fist-sized pump. The symbol is a stylised shape that dates back centuries.', 1],
            [1, 'Valentine\'s Day is named after a saint.', true, 'It is named after St Valentine, a martyr remembered on 14 February.', 1],
            [2, 'The wedding ring goes on the fourth finger because a vein runs straight from it to the heart.', false, 'The "vein of love" is an old Roman belief. Anatomically, every finger has similar veins.', 1],
            [1, 'The Taj Mahal was built as a tomb for an emperor\'s beloved wife.', true, 'Shah Jahan built it in memory of Mumtaz Mahal, who died in 1631.', 1],
            [2, 'The story of Romeo and Juliet takes place in less than a week.', true, 'From the ball to the tragedy, it all happens in about four days.', 1],
            [2, 'A married couple has been recorded staying together for over 80 years.', true, 'A few couples have celebrated more than 80 wedding anniversaries.', 1],
            [2, 'The oldest known love poem is about 4,000 years old.', true, 'A Sumerian clay tablet from around 2000 BC carries a love song to a king.', 1],
            [1, 'Many of the roses sold for Valentine\'s Day in Europe are grown in Kenya.', true, 'Farms around Lake Naivasha ship millions of stems every Valentine\'s season.', 1],
            [1, 'Cupid is the Roman god of love, and his Greek name is Eros.', true, 'In Greek myth, Eros is the god of love. The Romans called him Cupid.', 1],
            [2, 'Chocolate was first drunk, not eaten.', true, 'The Maya and Aztecs drank it as a bitter, frothy drink long before sweet bars existed.', 1],
            [1, 'In tennis, "love" means zero.', true, 'Nobody is certain where the term comes from, but a score of zero is called love.', 1],
            [2, '"Nakupenda" is how you say "I love you" in Swahili.', true, 'Nakupenda means "I love you". Say it to your partner right now.', 1],
        ],
    ],

    // ───────── Spotlight (creative, on the clock) ─────────
    'spotlight' => [
        'lovers' => [
            [1, 'Pitch the story of how you two met as a movie trailer.', 25],
            [1, 'Give your partner a nickname, then explain it in one sentence.', 20],
            [2, 'Recite a love poem to your partner. Make it up, and rhyme at least once.', 25],
            [2, 'Describe your partner as if you were a nature documentary narrator.', 20],
            [2, 'Plan the perfect date night for your partner in 20 seconds, out loud.', 25],
            [2, 'Sing the chorus of your song as a couple, badly, to your partner.', 20],
            [3, 'Give a mini wedding speech about your partner. Funny and heartfelt.', 25],
            [3, 'Write your partner a 15-second love letter, out loud, right now.', 20],
            [3, 'Propose a toast to your partner. Everyone raises a glass of water, juice or whatever they have.', 20],
            [3, 'Say three promises you want to keep to your partner.', 25],
        ],
    ],

    // ───────── Quickfire (timed lists) ─────────
    'quickfire' => [
        'lovers' => [
            [1, 'Name five things you love about your partner. Ten seconds!', 10], [1, 'Name three songs that remind you of your partner.', 10], [1, 'Name four places you want to travel to together.', 10],
            [2, 'Name five of your partner\'s favourite foods.', 10], [2, 'Name three of your partner\'s biggest dreams.', 12], [2, 'Name four things your partner does that make you smile.', 10],
            [3, 'Name five moments you have shared that you will never forget.', 12], [3, 'Name four things you would like to do together this year.', 10],
        ],
    ],

    // ───────── Fact or bluff ─────────
    'bluff' => [
        'lovers' => [
            [1, 'Tell the story of your first date. Is it exactly how it happened?'],
            [1, 'Describe the moment you first noticed your partner. True or invented?'],
            [2, 'Tell us about the sweetest thing your partner has ever done for you. Real?'],
            [2, 'Describe the most romantic place you have ever been together. True story?'],
            [2, 'Tell the story of your first argument. Did it happen like that?'],
            [3, 'Describe the moment you knew you loved your partner. Real or invented?'],
            [3, 'Tell us the most embarrassing thing that has ever happened between you two. True or bluff?'],
            [3, 'Describe the biggest surprise you have ever planned for your partner. Real?'],
        ],
    ],
];
