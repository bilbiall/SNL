<?php

/*
| Content for the two friend-group modes: "boyz" (guys together) and "girlies" (girls and their friends).
| No alcohol, nothing sexual, nothing cruel: banter, hype, dares, confessions and trivia. Forfeits are physical or silly
| (see 'penalties' in config/snl.php). Same shape as more-cards.php:
|
|  cards         kind => mode => intensity (1 Chill · 2 Normal · 3 Spicy) => [lines]
|  random_pick   mode => intensity => [lines using {target}]   (the wheel's deck)
|  facts         mode => [intensity, statement, answer, explanation, penalty]   (True or false)
|  spotlight     mode => [intensity, prompt, seconds]
|  quickfire     mode => [intensity, prompt, seconds]
|  bluff         mode => [intensity, prompt]
|
| Text may use {you}, {other}, {target}. Everything here is also editable in GameSet → Cards.
*/
return [

    'cards' => [

        // ───────── You do it ─────────
        'self' => [
            'boyz' => [
                1 => [
                    'Name your all-time starting XI in ten seconds, or do five push-ups.',
                    'Give your best commentator voice describing the last thing you ate.',
                    'Rate the last game or match you watched, out of ten, and defend your score.',
                    'Show the table the last photo in your camera roll (it can be a screenshot).',
                    'Do your best goal celebration, no holding back.',
                    'Say which friend here would survive longest in a zombie apocalypse, and why it is not you.',
                    'Do your best impression of a football pundit who has just watched a terrible match.',
                    'Flex for the table. Biggest flex gets a nod from everyone.',
                ],
                2 => [
                    'Tell the table the dumbest thing you have ever done to impress someone.',
                    'Rank everyone here by how likely they are to be late. Defend it.',
                    'Do your best "I am not mad, just disappointed" speech to the table.',
                    'Admit which of your habits is most annoying and what you are going to do about it (nothing?).',
                    'Name a skill you pretend to have but really do not.',
                    'Give a fake motivational speech as if you are the coach at half time, losing 3-0.',
                    'Reveal your most embarrassing nickname, and who gave it to you.',
                    'Do twelve squats while naming twelve footballers. No repeats.',
                ],
                3 => [
                    'Read out your most recent search history item, or do fifteen push-ups.',
                    'Tell the story of the most embarrassing thing that has happened to you at the gym or on a pitch.',
                    'Confess the last time you lied to get out of a plan, and what you said.',
                    'Admit the most childish thing you still do. The table votes: is it a 6 or a 10?',
                    'Let the person on your left send a harmless text to anyone you choose. Show the table first.',
                    'Do your most embarrassing dance move in front of everyone, with your serious face on.',
                    'Rate your own looks, honestly, out of ten. The table can bump you up or down.',
                    'Describe your worst haircut ever and who allowed it to happen.',
                ],
            ],
            'girlies' => [
                1 => [
                    'Give your best influencer voice and promote the nearest object like it is a must-have.',
                    'Rate your own outfit today out of ten, and tell the table the story behind it.',
                    'Pick your hype song and sing the first line with full confidence.',
                    'Show the table your most-used emoji and explain why you picked it.',
                    'Strike a pose like it is the cover of a magazine. The table scores it.',
                    'Say three things you love about yourself, with no apologising.',
                    'Do your best "get ready with me" voiceover for the next thirty seconds of your life.',
                    'Name your go-to comfort food, comfort show and comfort song. No thinking!',
                ],
                2 => [
                    'Tell the table about your worst outfit decision, and where you wore it.',
                    'Describe your dream holiday in one dramatic sentence, like a movie trailer.',
                    'Admit the last thing you bought and have never worn.',
                    'Reveal your guiltiest TV or reality show obsession and defend it.',
                    'Give your most honest ten-second pep talk to the person on your left.',
                    'Tell the story of the most awkward moment you have ever had in public.',
                    'Describe your ideal Sunday in exactly five words.',
                    'Do your best slow-motion hair flip. Judges: the whole table.',
                ],
                3 => [
                    'Tell the table about your most cringe crush, but you may leave the name out.',
                    'Read out the last voice note or message you sent your group chat, or give a pose forfeit.',
                    'Confess the last time you told a white lie to get out of a plan, and what you said.',
                    'Describe your worst hairstyle or makeover ever. Show a photo if it exists!',
                    'Admit something you pretend to like but secretly do not.',
                    'Give a full Oscar speech for winning "Most Dramatic Person Here".',
                    'Show the table the oldest photo of yourself on your phone, and tell the story.',
                    'Tell the table your most embarrassing moment on social media.',
                ],
            ],
        ],

        // ───────── Pick a friend (roller chooses) ─────────
        'pick' => [
            'boyz' => [
                1 => [
                    'Pick who would be the best captain of the team. They give a short speech.',
                    'Pick who has the best taste in music. They name their top three.',
                    'Pick who is the best cook here. They tell us their signature dish.',
                    'Pick who would win a race here. They stand up and do a victory pose.',
                    'Pick who gives the best advice. They give one piece, right now.',
                ],
                2 => [
                    'Pick who is most likely to become a millionaire. They share their business idea.',
                    'Pick who is most likely to be late to their own wedding. They do five push-ups.',
                    'Pick who is the most competitive. They must lose gracefully at rock-paper-scissors to you.',
                    'Pick who is the worst driver (jokingly!). They defend themselves in ten seconds.',
                    'Pick who would last longest on a survival show. They explain their plan.',
                ],
                3 => [
                    'Pick who is most likely to cry at a movie. They name the movie that got them.',
                    'Pick who has the worst taste in clothes. They must show their best (or worst) fashion moment.',
                    'Pick who is the biggest show-off. They have ten seconds to show off something real.',
                    'Pick who is most likely to be famous one day. They give an acceptance speech.',
                    'Pick the friend with the biggest ego. They must say something humble about themselves.',
                ],
            ],
            'girlies' => [
                1 => [
                    'Pick who is the best dressed here. They tell us where the outfit is from.',
                    'Pick who gives the best advice. They give some, right now.',
                    'Pick who has the best laugh. They show it off.',
                    'Pick the best dancer. They show one move.',
                    'Pick who is the most organised. They share their best tip.',
                ],
                2 => [
                    'Pick who is most likely to become famous. They give a short thank-you speech.',
                    'Pick who is the most dramatic here. They act out their most dramatic moment.',
                    'Pick who is always late. They do a runway walk as an apology.',
                    'Pick who would be the best boss. They give a fake pep talk.',
                    'Pick who takes the best photos. They take a candid shot of the table.',
                ],
                3 => [
                    'Pick who is most likely to ghost a bad date. They tell the story of their best escape.',
                    'Pick who has the best taste in men or women (their choice!). They describe their type in five words.',
                    'Pick who is the biggest gossip (lovingly). They tell a harmless secret about themselves.',
                    'Pick who is the biggest flirt. They flirt with an object of your choice.',
                    'Pick who would marry first. They describe their dream wedding in ten seconds.',
                ],
            ],
        ],

        // ───────── Everyone ─────────
        'everyone' => [
            'boyz' => [
                1 => [
                    'Everyone names their favourite footballer on three. The most popular answer wins; the rest do three push-ups.',
                    'Everyone shows their best flex at the same time. Roller picks the winner.',
                    'Everyone says one thing they are good at, with no modesty allowed.',
                    'Everyone does their best victory celebration on three.',
                    'Everyone names their dream car. Roller judges the best choice.',
                ],
                2 => [
                    'Everyone says the last thing they Googled. Anyone who refuses does ten push-ups.',
                    'Everyone points at who is the funniest here on three. The most pointed-at person wins applause.',
                    'Everyone does their best impression of another player. Loudest laugh wins.',
                    'Everyone names the worst advice they ever followed. Funniest answer wins.',
                    'Everyone says their biggest red flag as a friend. Be honest!',
                ],
                3 => [
                    'Everyone points on three: who is most likely to get lost with GPS on. They do a forfeit.',
                    'Everyone shares the last photo they took. No deleting first!',
                    'Everyone confesses one lie they told this year. The roller judges the biggest.',
                    'Everyone rates the roller\'s outfit out of ten, out loud, on three.',
                    'Everyone shares the most embarrassing text they have ever sent to the wrong person.',
                ],
            ],
            'girlies' => [
                1 => [
                    'Everyone gives the person on their left a genuine compliment.',
                    'Everyone names their favourite song to dance to. The table picks the winner.',
                    'Everyone shows their phone wallpaper. No cheating!',
                    'Everyone does a pose on three. The roller picks the best.',
                    'Everyone names their dream holiday spot at the same time.',
                ],
                2 => [
                    'Everyone confesses their most-used emoji. The most embarrassing wins.',
                    'Everyone points to who is the most likely to be late. They do a runway walk.',
                    'Everyone shares the worst gift they have ever received.',
                    'Everyone tells their most cringe selfie moment.',
                    'Everyone says one thing they would change about their morning routine.',
                ],
                3 => [
                    'Everyone shows their last three photos in their camera roll. No deleting!',
                    'Everyone points on three: who is the most likely to text their ex. They do a forfeit.',
                    'Everyone confesses a harmless lie they have told this year.',
                    'Everyone names the most dramatic thing they have ever done over a small thing.',
                    'Everyone describes their worst date or hang-out in one sentence.',
                ],
            ],
        ],

        // ───────── Duel (loser pays) ─────────
        'duel' => [
            'boyz' => [
                1 => ['The loser does five push-ups and says "respect" to the winner.', 'The loser gives the winner a slow clap and a bow.'],
                2 => ['The loser does ten push-ups while the winner counts out loud.', 'The loser must name five footballers in ten seconds or do ten squats.', 'The loser admits one thing the winner is better at.'],
                3 => ['The loser does a plank for 30 seconds while the winner talks smack (kindly).', 'The loser tells the table the most embarrassing thing about themselves.'],
            ],
            'girlies' => [
                1 => ['The loser gives the winner a genuine compliment.', 'The loser does a runway walk across the room.'],
                2 => ['The loser sings the first line of the winner\'s favourite song.', 'The loser tells the table one thing they admire about the winner.', 'The loser does an Oscar speech thanking the winner.'],
                3 => ['The loser does a dramatic soap-opera scene with the winner picking the line.', 'The loser tells the table their most embarrassing story.'],
            ],
        ],

        // ───────── New rule ─────────
        'rule' => [
            'boyz' => [
                1 => ['New rule: nobody says "bro". Slip up, do three push-ups.', 'New rule: everyone must start sentences with "Listen..." Slip up, do three squats.', 'New rule: no phones on the table. Slip up, do five push-ups.'],
                2 => ['New rule: call everyone "Coach". Slip up, do five push-ups.', 'New rule: you must clap before you speak. Slip up, do five squats.', 'New rule: no saying anyone\'s name. Slip up, do five push-ups.', 'New rule: speak like a football commentator. Slip up, do ten squats.'],
                3 => ['New rule: end every sentence with "no cap". Slip up, do ten push-ups.', 'New rule: nobody says "I". Say "this man". Slip up, do ten squats.', 'New rule: speak only in questions. Slip up, do ten push-ups.'],
            ],
            'girlies' => [
                1 => ['New rule: end every sentence with "bestie". Slip up, strike a pose.', 'New rule: no saying "like". Slip up, give a compliment.', 'New rule: everyone speaks in a posh accent. Slip up, do a runway walk.'],
                2 => ['New rule: call everyone "queen". Slip up, strike a pose.', 'New rule: you must whisper everything. Slip up, give a compliment.', 'New rule: nobody says anyone\'s name. Slip up, do a runway walk.', 'New rule: narrate your own life in third person. Slip up, strike a pose.'],
                3 => ['New rule: start every sentence with "Honestly...". Slip up, do a runway walk.', 'New rule: everything is dramatic: gasp before answering. Slip up, sing a line.', 'New rule: no laughing! Slip up, give a compliment to the table.'],
            ],
        ],

        // ───────── Kind words / hype ─────────
        'heart' => [
            'boyz' => [
                1 => ['Tell {other} one thing you respect about them.', 'Give {other} a respectful nod and say what makes them a good friend.', 'Name something {other} is great at.'],
                2 => ['Tell {other} about a time they had your back.', 'Say what you would want {other} to be famous for.', 'Share a favourite memory you have with {other}.'],
                3 => ['Tell {other} something you have never said out loud, but meant: a thank-you.', 'Say the best thing {other} has taught you.', 'Give {other} a short, honest, brotherly speech.'],
            ],
            'girlies' => [
                1 => ['Give {other} a heartfelt compliment on something that is not their looks.', 'Tell {other} what you admire about them.', 'Name three words that describe {other}.'],
                2 => ['Tell {other} about a time they made you feel good.', 'Say what superpower {other} has as a friend.', 'Share a favourite memory you have with {other}.'],
                3 => ['Tell {other} the thing you have always wanted to thank them for.', 'Give {other} a short, honest, hype-woman speech.', 'Say what {other} deserves to hear more often.'],
            ],
        ],

        // ───────── Group games ─────────
        'groupgame' => [
            'boyz' => [
                1 => ['Everybody names a footballer, round the table, no repeats. First to stall does five push-ups.', 'Everybody names a car brand, round the table. First to repeat or stall pays.', 'Name a country at a time, round the table, each starting with the last letter. First to stall pays.'],
                2 => ['Everybody names a sport, round the table, with no repeats. First to stall pays.', 'Everybody names a movie starring an action hero. First to stall pays.', 'Everybody names a Kenyan musician, round the table. First to stall pays.'],
                3 => ['Rapid fire: everybody names a famous person with the same first letter as the roller. First to stall pays.', 'Everybody names a country that has hosted the Olympics. First to stall or repeat pays.'],
            ],
            'girlies' => [
                1 => ['Everybody names a singer, round the table, no repeats. First to stall does a pose.', 'Everybody names a colour of nail polish. First to repeat or stall pays.', 'Name a movie, then the next person names one starting with its last letter. First to stall pays.'],
                2 => ['Everybody names a celebrity with the same first letter as the roller. First to stall pays.', 'Everybody names a fashion brand, round the table. First to stall pays.', 'Everybody names a Kenyan actress or singer. First to stall pays.'],
                3 => ['Rapid fire: everybody names a famous couple. First to stall or repeat pays.', 'Everybody names a song with a name in the title. First to stall pays.'],
            ],
        ],
    ],

    // The wheel's deck: {target} is the player the wheel picked.
    'random_pick' => [
        'boyz' => [
            1 => ['The wheel picked {target}! Give the table your best goal celebration.', '{target} was chosen. Say who you think is the funniest person here.', 'The wheel chose {target}: do five push-ups, no complaining.'],
            2 => ['{target}, the wheel says: tell us your most useless talent.', 'Fate chose {target}: do your best impression of a friend here.', 'The wheel picked {target}! Name three footballers in five seconds or do ten squats.'],
            3 => ['The wheel is ruthless: {target}, confess your worst habit.', '{target} was chosen: tell the table about your most embarrassing moment.', 'Fate chose {target}: show us the last photo in your camera roll.'],
        ],
        'girlies' => [
            1 => ['The wheel picked {target}! Give the table your best runway walk.', '{target} was chosen. Give the person on your right a compliment.', 'The wheel chose {target}: strike your best pose.'],
            2 => ['{target}, the wheel says: tell us your most useless talent.', 'Fate chose {target}: do an impression of a friend here.', 'The wheel picked {target}! Sing the first line of your favourite song.'],
            3 => ['The wheel is ruthless: {target}, confess your biggest guilty pleasure.', '{target} was chosen: tell the table your most embarrassing moment.', 'Fate chose {target}: show us the last photo in your camera roll.'],
        ],
    ],

    // ───────── True or false ─────────
    'facts' => [
        'boyz' => [
            [1, 'A marathon is 42.195 kilometres long.', true, 'It is 26 miles and 385 yards, a distance set at the 1908 London Olympics.', 1],
            [1, 'Eliud Kipchoge ran an official, record-eligible marathon in under two hours in Vienna in 2019.', false, 'He ran 1:59:40, but with rotating pacemakers, so it was not ratified as a world record.', 1],
            [1, 'A kilo of muscle weighs more than a kilo of fat.', false, 'A kilo is a kilo. Muscle is just denser, so it takes up less space.', 1],
            [2, 'Doing crunches burns fat from your belly specifically.', false, 'Spot reduction is a myth. Your body loses fat from all over, not just where you train.', 1],
            [1, 'The Ferrari prancing-horse badge was inspired by a World War I fighter pilot.', true, 'Francesco Baracca painted the horse on his plane, and his family later suggested it to Enzo Ferrari.', 1],
            [2, 'Henry Ford invented the first car.', false, 'Karl Benz built the first practical petrol car in 1885–86. Ford made cars cheap with mass production.', 1],
            [1, 'Basketball was invented by a PE teacher.', true, 'James Naismith created it in 1891 so his students had a game to play indoors in winter.', 1],
            [2, 'Bruce Lee was once the cha-cha champion of Hong Kong.', true, 'He won the colony\'s cha-cha championship in 1958.', 1],
            [2, 'An African team has won the football World Cup.', false, 'Morocco came closest, reaching the semi-finals in 2022.', 1],
            [2, 'Usain Bolt\'s 100 metres world record is 9.58 seconds.', true, 'He ran it in Berlin in 2009, and it still stands.', 1],
            [1, 'Cracking your knuckles gives you arthritis.', false, 'Studies have found no link. The pop is just gas bubbles in the joint fluid.', 1],
            [2, 'A cheetah can reach about 100 kilometres an hour.', true, 'It is the fastest land animal, but only over short bursts of roughly 20 seconds.', 1],
            [2, 'Lightning never strikes the same place twice.', false, 'The Empire State Building is hit around 20 to 25 times a year.', 1],
            [3, 'Football pitches must be exactly the same size in every professional match.', false, 'The laws of the game allow a range of lengths and widths, so grounds differ.', 2],
        ],
        'girlies' => [
            [1, 'Shaving makes hair grow back thicker.', false, 'It only looks thicker because the cut tip is blunt. The hair itself does not change.', 1],
            [1, 'Humans only use 10% of their brains.', false, 'Brain scans show activity across the whole brain, even during sleep.', 1],
            [1, 'Honey never spoils.', true, 'Edible honey has been found in ancient Egyptian tombs. Its low moisture and acidity keep it safe.', 1],
            [1, 'Butterflies taste with their feet.', true, 'They have taste sensors on their feet, which helps them find the right plants.', 1],
            [2, 'High heels were first worn by men.', true, 'They began as riding shoes for Persian cavalry, then became fashionable in European courts.', 1],
            [2, 'The bikini was named after a nuclear-test site.', true, 'It was named after Bikini Atoll in 1946, a few days after tests were held there.', 1],
            [2, 'Vanilla is the second most expensive spice in the world.', true, 'Only saffron costs more, because vanilla is pollinated by hand.', 1],
            [2, 'Fingernails grow faster than toenails.', true, 'About three times faster, roughly 3 mm a month against 1 mm.', 1],
            [1, 'You lose most of your body heat through your head.', false, 'You lose heat from any uncovered part of the body, in proportion to its size.', 1],
            [2, 'A day on Venus is longer than its year.', true, 'Venus spins so slowly that one rotation takes longer than a trip around the Sun.', 1],
            [1, 'Going out with wet hair gives you a cold.', false, 'Colds are caused by viruses, not by cold or wet hair.', 1],
            [2, 'Kenya is the world\'s biggest exporter of cut flowers.', false, 'The Netherlands is, though Kenya is one of the top suppliers to Europe.', 1],
            [1, 'Chocolate is dangerous for dogs.', true, 'It contains theobromine, which dogs cannot process well.', 1],
            [3, 'Strawberries are berries, but bananas are not.', false, 'It is the other way round: botanically, bananas are berries and strawberries are not.', 2],
        ],
    ],

    // ───────── Spotlight (creative, on the clock) ─────────
    'spotlight' => [
        'boyz' => [
            [1, 'Sell the table the worst product ever, like a TV advert, in 15 seconds.', 20],
            [1, 'Commentate on {other} drinking a glass of water like it is the World Cup final.', 20],
            [2, 'Give a pre-match team talk to {other} before the biggest game of their life.', 20],
            [2, 'Pitch a business idea that will make you a millionaire. Keep a straight face.', 25],
            [2, 'Act out your most embarrassing moment on a pitch or at the gym, with no words.', 20],
            [2, 'Give a eulogy for a pair of shoes you loved and lost.', 20],
            [3, 'Defend a terrible opinion for 15 seconds as if your life depends on it.', 20],
            [3, 'Do your best impression of {other} at their most stubborn.', 20],
            [3, 'Give a wedding best-man speech for {other}, making it both funny and kind.', 25],
            [3, 'Rap a roast of yourself. Kindly. For 15 seconds.', 20],
        ],
        'girlies' => [
            [1, 'Advertise your favourite snack like a luxury perfume advert, in 15 seconds.', 20],
            [1, 'Narrate {other} walking across the room like a nature documentary.', 20],
            [2, 'Give a pep talk to {other} before the biggest day of their life.', 20],
            [2, 'Pitch a reality show starring this friend group. Title, plot and the first episode.', 25],
            [2, 'Act out your most embarrassing moment, with no words.', 20],
            [2, 'Give a farewell speech to a dress you loved and lost.', 20],
            [3, 'Defend a terrible opinion for 15 seconds as if your life depends on it.', 20],
            [3, 'Do your best impression of {other} at their most dramatic.', 20],
            [3, 'Give a maid-of-honour speech for {other}, funny and heartfelt.', 25],
            [3, 'Perform a dramatic monologue about losing your phone charger.', 20],
        ],
    ],

    // ───────── Quickfire (timed lists) ─────────
    'quickfire' => [
        'boyz' => [
            [1, 'Name five footballers. Ten seconds!', 10], [1, 'Name four car brands. Go!', 10], [1, 'Name five sports. Quick!', 10],
            [2, 'Name five African countries that have qualified for a World Cup.', 12], [2, 'Name five gym exercises in ten seconds.', 10], [2, 'Name five athletes from Kenya.', 12],
            [3, 'Name five NBA or Premier League players with the same first letter, no repeats.', 12], [3, 'Name four heavyweight boxers. Go!', 10],
        ],
        'girlies' => [
            [1, 'Name five singers. Ten seconds!', 10], [1, 'Name four nail polish colours. Go!', 10], [1, 'Name five fashion brands. Quick!', 10],
            [2, 'Name five Kenyan songs or artists. Ten seconds!', 12], [2, 'Name five movies starring women. Go!', 10], [2, 'Name five things in a handbag.', 10],
            [3, 'Name five female artists with the same first letter, no repeats.', 12], [3, 'Name four reality shows. Go!', 10],
        ],
    ],

    // ───────── Fact or bluff ─────────
    'bluff' => [
        'boyz' => [
            [1, 'Tell us about the best goal you have ever scored or seen. True story?'],
            [1, 'Describe the most impressive thing you have done at the gym. Real, or all in your head?'],
            [2, 'Tell the table about the worst fight you have ever had with a friend. Did it really happen?'],
            [2, 'Describe the time you got lost somewhere. True or invented?'],
            [2, 'Tell the story of your most embarrassing moment on a date. Real?'],
            [3, 'Tell us about the biggest lie you ever told to get out of trouble. True or bluff?'],
            [3, 'Describe the closest you have come to getting caught doing something you should not have. Real or invented?'],
            [3, 'Name the most expensive thing you have ever broken, and how. True story?'],
        ],
        'girlies' => [
            [1, 'Tell us about the best outfit you have ever worn. True story?'],
            [1, 'Describe the best holiday you have ever had. Real, or all in your head?'],
            [2, 'Tell the table about the worst argument you have had with a friend. Did it really happen?'],
            [2, 'Describe the time you got lost somewhere. True or invented?'],
            [2, 'Tell the story of your most awkward date. Real?'],
            [3, 'Tell us about the biggest lie you ever told to get out of a plan. True or bluff?'],
            [3, 'Describe the closest you have come to getting caught gossiping. Real or invented?'],
            [3, 'Name the most embarrassing thing you have ever done for a crush. True story?'],
        ],
    ],
];
