<?php

/*
| Content pack 2 — bigger decks and templated cards so a game stays fresh for a long time at every level (esp. Spicy).
|   words      [slot, mode, intensity, text]   word lists for {category}, {letter} and friends
|   quickfire  [intensity, text, seconds]      10-second challenges (many are templates: {category}, {letter})
|   cards      kind => mode => intensity => [lines]
|   facts      [intensity, statement, answer, explanation, penalty]
|   spotlight  [intensity, prompt, seconds]
|   bluff      [mode, intensity, prompt]
*/
return [

    'words' => array_merge(
        // {category} — plural nouns, grouped by how wild they are
        array_map(fn ($t) => ['category', 'all', 1, $t], [
            'fruits', 'vegetables', 'animals that live in water', 'things in a kitchen', 'countries in Africa', 'cartoon characters', 'board games',
            'things that are round', 'things you wear on your head', 'car brands', 'school subjects', 'sports', 'musical instruments', 'ice-cream flavours',
            'things in a classroom', 'superheroes', 'Disney movies', 'things in a handbag', 'things at a wedding', 'things at the beach', 'birds',
            'TV shows', 'cities in Europe', 'fast-food chains', 'kinds of dance', 'things with wheels', 'pizza toppings', 'snacks', 'things in a garden',
            'farm animals', 'things that are yellow', 'footballers', 'things you plug in', 'types of weather', 'things in a supermarket', 'breakfast foods',
        ]),
        array_map(fn ($t) => ['category', 'all', 2, $t], [
            'pop stars', 'rappers', 'movie villains', 'social media apps', 'dating apps', 'things you do on a first date', 'excuses for being late',
            'famous couples', 'romantic movies', 'songs with a name in the title', 'words that rhyme with "love"', 'things people lie about', 'reasons to leave a party',
            'group-chat names', 'things in a gym', 'celebrity chefs', 'streaming shows', 'things you find in a club', 'words for "friend"', 'Gen Z slang words',
            'things you say when you are tired', 'songs about summer', 'things you do on a road trip', 'reality-TV shows', 'famous duos',
        ]),
        array_map(fn ($t) => ['category', 'all', 3, $t], [
            'dating red flags', 'things you should never text an ex', 'pickup lines', 'terrible first-date ideas', 'guilty-pleasure songs', 'regrets from a night out',
            'lies you tell your parents', 'awkward things to say at a wedding', 'ways to say "I\'m fine" when you\'re not', 'nicknames for an ex', 'relationship deal-breakers',
            'terrible tattoo ideas', 'excuses to leave a date early', 'things you do when nobody is watching', 'worst things to hear at 2am', 'cringe things you did as a teenager',
        ]),
        array_map(fn ($t) => ['category', 'drinks', 2, $t], ['cocktails', 'beers and ciders', 'things in a bar', 'drinking games', 'shots', 'wines', 'drinks that start a good night']),
        array_map(fn ($t) => ['category', 'drinks', 3, $t], ['hangover cures', 'things you said while drunk', 'bad ideas after midnight', 'drinks you swore you would never touch again', 'things you shouldn\'t do after three drinks']),
        array_map(fn ($t) => ['category', 'family', 2, $t], ['things at a birthday party', 'things you can do on a rainy day', 'bedtime stories', 'family traditions', 'board-game pieces']),
        // {letter}
        array_map(fn ($t) => ['letter', 'all', 1, $t], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'W']),
        // more scene / vibe / hot-topic words, with a spicy tail
        array_map(fn ($t) => ['scene', 'all', 2, $t], ['a blind date', 'a surprise visit from your ex', 'the group-chat going silent', 'your phone at 1% battery', 'a long queue for the toilet', 'someone playing guitar uninvited', 'your boss adding you on social media']),
        array_map(fn ($t) => ['scene', 'all', 3, $t], ['sliding into someone\'s DMs', 'a bad haircut before a big night', 'the morning after a very good night', 'a very awkward lift ride', 'sending a text to the wrong person', 'getting caught staring']),
        array_map(fn ($t) => ['vibe', 'all', 3, $t], ['unhinged energy', 'toxic ex energy', 'last-call energy', 'who-let-me-text-that energy', 'delulu era', 'villain monologue']),
        array_map(fn ($t) => ['hot_topic', 'all', 3, $t], ['Going back to your ex is always a mistake', 'Sharing your phone passcode is a green flag', 'You should be able to tell when someone is lying', 'The person who texts first loses', 'Everyone has a secret they would take to the grave', 'Two drinks is enough for a perfect night']),
    ),

    'quickfire' => array_merge(
        [
            // templates — every {category}/{letter} makes it a different challenge
            [1, 'Name five {category}. Ten seconds!', 10], [1, 'Name four {category} starting with {letter}.', 12], [1, 'Name three {category} before the clock runs out.', 8],
            [1, '{category}: you need five. Go!', 10], [1, 'Name three {category} that start with {letter}.', 10],
            [2, 'Name five {category}. Ten seconds!', 10], [2, 'Name four {category} starting with {letter}.', 12], [2, 'Name three {category} before the clock runs out.', 8],
            [2, '{category}: you need five. Go!', 10], [2, 'Name six {category} — no repeats, no ums.', 12], [2, 'Name three {category} that start with {letter}.', 10],
            [3, 'Spicy round — name five {category}. Ten seconds!', 10], [3, 'Spicy round — name four {category}, no hesitating.', 10], [3, 'Name three {category} — one for each hand-clap.', 8],
            [3, '{category}: six of them, ten seconds, and they all have to be different.', 10], [3, 'Name three {category} that start with {letter}.', 10],
            // handwritten extras
            [1, 'Say the alphabet backwards from M.', 12], [1, 'Name three things you can do with a paperclip.', 8], [1, 'Name five things you can hold in one hand.', 8],
            [1, 'Count backwards from twenty in threes.', 12], [1, 'Name four things that make a noise.', 8], [1, 'Name five words that end in "ing".', 8],
            [1, 'Name three ways to cook an egg.', 8], [1, 'Name four jobs that start with the letter P.', 10], [1, 'Hum three bars of any song — the table guesses it.', 12],
            [2, 'Rap the first four words of any song, then make it rhyme.', 12], [2, 'Name five apps without looking at your phone.', 8], [2, 'List three red flags and three green flags — fast.', 10],
            [2, 'Name four songs from the year you were born.', 12], [2, 'Name five things you shouldn\'t say on a first date.', 10], [2, 'Say "unique New York" five times fast.', 8],
            [2, 'Give three compliments to the person on your left.', 10], [2, 'Name four people in your contacts with the same first letter.', 10],
            [3, 'Name three secrets you would never tell a stranger — just the topics.', 10], [3, 'Name five things you\'d do if you were invisible for an hour.', 10],
            [3, 'Say three things you love about the person on your right — and one you tolerate.', 12], [3, 'Name four ways to apologise to someone you ghosted.', 10],
            [3, 'Name five things that go wrong on a night out.', 10], [3, 'Whisper a rumour about yourself — the table guesses if it\'s true.', 10],
            [3, 'Name three songs you would play to win somebody back.', 10], [3, 'List four embarrassing things in your search history — just the categories.', 10],
        ]
    ),

    'cards' => [
        'groupgame' => [
            'all' => [
                1 => [
                    'Category: {category}! One answer each, round the table — stall or repeat and you pay the penalty.',
                    'Everybody names a {category} beginning with {letter}, one after another. First to stall pays.',
                    'Mime battle: the roller mimes something, the table guesses — the guesser who is first picks the next mime.',
                    'Everybody says "cheese" with a totally different face. Funniest face (table vote) picks who pays.',
                    'Staring round: everyone locks eyes with the person opposite — first to laugh pays.',
                    'Everybody shares their lucky number and why. The roller picks the best answer — everyone else pays.',
                    'The roller starts a story with one word. Everyone adds a word in turn. First to break the sentence pays.',
                    'Left or right? Everybody points left or right on three. The minority pays.',
                    'Everybody names the first song they heard today. Most embarrassing (table vote) pays.',
                    'Everybody does a one-second impression of the person on their left.',
                ],
                2 => [
                    'Category: {category}! Round the table, no repeats — the first to stall or repeat pays.',
                    'Everybody names a {category} beginning with {letter}. First to run dry pays.',
                    'Roller picks a song title — everyone has to sing one line from a song with a word from it.',
                    'Everybody guesses how many photos are on the roller\'s phone. Furthest guess pays.',
                    'Never have I ever (clean edition): the roller goes first, anyone who has done it takes the penalty.',
                    'Everybody says one thing they\'re secretly proud of. The most surprising (table vote) pays no penalty — everyone else does.',
                    'Speed round: the roller points and says a category, the pointed-at person has two seconds. Fail and pay.',
                    'Who would survive longest in a zombie film? Everybody points on three — last place pays.',
                    'Everybody puts a finger on their nose. The roller says "go" at a random moment — last finger pays.',
                    'Everybody does their best movie-trailer voice for the word "tonight". Table votes — loser pays.',
                    'The roller gives a letter. Everybody must say a movie title that starts with it. First to fail pays.',
                    'Everybody names a {category}. The roller picks the winner — the loser pays.',
                ],
                3 => [
                    'Hot take: "{hot_topic}". Everybody picks a side — the smaller side pays.',
                    'Category: {category}. Round the table, no repeats — first to stall pays double.',
                    'Everybody names a {category} beginning with {letter}. The first to stall pays.',
                    'Truth or sip: the roller asks one question — everyone answers honestly or pays.',
                    'Everybody writes a nickname for the roller in the air. The roller crowns the best — the rest pay.',
                    'Everybody finishes the sentence: "My most chaotic habit is…". The roller picks the funniest — the others pay.',
                    'Three-count confession: on three, everyone looks at the person they\'d trust most with a secret.',
                    'Who is most likely to… (the roller invents it). Everybody points on three. The most-pointed-at pays double.',
                    'Everybody says the last thing they searched for. Refuse and you pay.',
                    'Red flag round: everybody gives one of their own red flags. Worst (table vote) pays.',
                    'Everybody picks someone to swap phones with for ten seconds — no peeking at messages. Anyone who refuses pays.',
                    'Everybody rates the roller\'s taste in music out of ten. Lowest scorer must give a reason — or pay.',
                    'Two minutes of gossip: everybody must say one nice thing about someone not in the room — pay if you can\'t.',
                    'Everyone swaps seats. First to sit back down in their old seat pays.',
                ],
            ],
            'drinks' => [
                1 => ['Cheers train: everybody clinks with the person on their left, then the one on their right — last clink sips.', 'Everyone raises a glass to the roller, who picks the loudest cheer — the quietest sips.'],
                2 => ['Categories: {category}. Go round — first to stall takes a sip.', 'Sip democracy: the roller names a rule; everyone votes — the minority sips.', 'Everybody who has been to a wedding this year sips; the roller picks who gives the toast.'],
                3 => ['Waterfall, spicy edition: the roller starts, everyone drinks until the roller stops, and then each person stops in turn.', 'Everybody who has ever sent a text they regretted takes a shot — the roller reads one out (no names).', 'Bartender\'s dare: the roller names a made-up cocktail; everyone must say what\'s in it. Weakest answer takes a shot.'],
            ],
            'family' => [
                1 => ['Everybody does their best animal impression at the same time. The roller guesses who is who.', 'Everyone shares their favourite family memory in one sentence.'],
                2 => ['Everybody acts out their favourite cartoon character. The table guesses — the slowest guess does a silly walk.', 'Everybody says what they would do with a day off — the boringest answer does ten star jumps.'],
                3 => ['Everybody shares their biggest fear (the funny kind) in five words. The table votes on the best — the others do a silly dance.', 'Everybody says a compliment that rhymes. First to run out does a robot dance.'],
            ],
        ],
        'gift' => [
            'all' => [
                1 => ['Lucky find! A shield rolled out of the confetti.', 'A tiny golden chest pops open — a free power-up.', 'You tripped over a gift. Lucky you!', 'A stranger tosses you a present.', 'The board winks and slips you a gift.', 'Something shiny glints under the square — it\'s yours.'],
                2 => ['Pocket check — a free shield was in there all along.', 'Finders keepers: a power-up appeared out of nowhere.', 'The game owes you one. Here you go.', 'A mysterious envelope slides across the board — power-up inside.', 'Lucky square! A free gift for you.'],
                3 => ['The house slips you a favour. Don\'t tell the others.', 'A suspicious bribe from the game: a free power-up.', 'You didn\'t find it, it found you: a free gift.', 'The board feels guilty. Here, take this.'],
            ],
        ],
        'rollagain' => [
            'all' => [
                1 => ['Lucky break — roll again!', 'The dice want another go. Roll again!', 'Bonus turn! Roll again.', 'Free spin on the dice — roll again!', 'You earned an extra roll. Go on!', 'The board likes you — roll again.'],
                2 => ['Double trouble! Roll again.', 'Tailwind! You get another roll.', 'The universe says: again!', 'Plot twist — it\'s still your turn.', 'Encore! Roll again.'],
                3 => ['Chaos approved — roll again!', 'Fortune favours the bold: another roll.', 'The board refuses to let you stop. Roll again.', 'You\'re on a roll. Literally. Again!'],
            ],
        ],
        'neutral' => [
            'all' => [
                1 => [
                    'Take a breath. Nothing here.', 'Free square. Stretch like a cat.', 'Quiet corner of the board. Enjoy it.', 'No action — only vibes.', 'Plot armour: nothing happens.', 'Check your posture. Done. Carry on.',
                    'Time to hydrate — water counts.', 'The board is napping. Don\'t wake it.', 'Safe harbour — for now.', 'Pat yourself on the back. You earned it.', 'Free square. Name your favourite snack silently.',
                    'Nothing to see here, move along.', 'The calm before the next square.', 'Free square: compliment your own outfit.',
                ],
                2 => [
                    'Free square — but everybody is watching you. Act natural.', 'Nothing happens. Suspicious, right?', 'Calm square. Use it to plan your next move (or your next sip).', 'Free pass. Spend it wisely — by doing nothing.',
                    'You\'re safe. The others definitely aren\'t.', 'Take a moment to judge everyone silently.', 'Free square. Strike a victory pose for no reason.', 'Nothing here — but someone will be jealous.',
                    'Peace and quiet. It won\'t last.', 'A blank square. The universe saves its energy.',
                ],
                3 => [
                    'Free square. The silence is deafening.', 'Nothing happens — which is exactly what the next square is hoping you think.', 'Safe… until the next roll.', 'You get a pass. Don\'t get comfortable.',
                    'Calm waters. Sharks are circling.', 'A free square — the calm before someone\'s bad decision.', 'Nothing to confess here. Yet.', 'Enjoy the quiet. Chaos has your number.',
                    'You dodged this one. Who is plotting something?', 'A moment of peace for the wicked.',
                ],
            ],
            'drinks' => [
                1 => ['Water break — your future self says thanks.', 'Pace yourself — a snack is a good idea.'],
                2 => ['Free square. Top up your glass — you\'ve got time.', 'Nothing happens. Clink your glass with someone anyway.'],
                3 => ['Free square. Be honest: are you on water yet?', 'Nothing happens — this is a good moment to check the time and your phone battery.'],
            ],
            'family' => [
                1 => ['Free square — a big smile for everyone!', 'Nothing happens. Give someone a high five.'],
                2 => ['Free square. Everybody wave at the roller!', 'Nothing here — tell someone one thing you\'re looking forward to.'],
                3 => ['Free square. Do your best dramatic gasp.', 'Nothing here — whisper a secret handshake to the person on your right.'],
            ],
        ],

        // more spice for the core decks ────────────────────────────────────────────────────────────
        'self' => [
            'drinks' => [
                3 => [
                    'Take a shot if you\'ve ever pretended to like a gift.', 'Take a shot if you have a screenshot you should delete.', 'Take a shot if you\'ve stalked an ex this month.',
                    'Take a shot if you\'ve ever cried in a club bathroom.', 'Take a shot if you\'ve fallen in love with a stranger in under a minute.', 'Take a shot if you\'ve lied about where you were.',
                    'Take a shot if you\'ve sent a text and then thrown your phone.', 'Take a shot if you\'ve ever faked a laugh at a joke your date made.', 'Take a shot if you\'ve borrowed money and "forgotten" to pay back.',
                    'Take a shot if you have an unread message you\'re avoiding.', 'Take a shot if you\'ve ever crashed a wedding.', 'Take a shot if you\'ve kissed someone whose name you didn\'t know.',
                    'Take a shot if you\'ve ever left a party without saying goodbye to anyone.', 'Take a shot if you\'ve typed a whole paragraph and then deleted it.', 'Take a shot if you\'ve ever said "I\'m five minutes away" while still in bed.',
                    'Take a shot if you have more than three unread group chats.', 'Take a shot if you\'ve ever pretended not to see someone you know.', 'Take a shot if you\'ve ever re-read a conversation looking for hidden meaning.',
                    'Take a shot if you\'ve snooped through a friend\'s bathroom cabinet.', 'Take a shot if you\'ve got a talent you\'d never show on a first date.',
                ],
                2 => ['Take a shot if you\'ve ever mixed up two people\'s names — out loud.', 'Take a shot if you\'ve fallen asleep at a party.', 'Take a shot if you\'ve ever walked into a glass door.', 'Take a shot if you\'ve waved at someone who wasn\'t waving at you.', 'Take a shot if you\'ve accidentally liked something you shouldn\'t have.'],
            ],
            'family' => [
                3 => ['Reveal the most embarrassing thing in your camera roll (describe it).', 'Do an impression of someone here until your next turn.', 'Speak only in questions until your next turn.', 'Show the table your best "I just won the lottery" face.', 'Act out your morning routine in 10 seconds.', 'Say a tongue twister of the table\'s choice — if you fail, do a silly walk.', 'Sing the first line of your favourite song in a squeaky voice.', 'Give a dramatic speech accepting an award for "Best Procrastinator".'],
                2 => ['Walk like a crab to the other side of the table and back.', 'Describe your favourite food without using the words "tasty" or "yum".', 'Pretend you\'re a news reporter describing the room.', 'Do your best impression of a baby giraffe learning to walk.'],
            ],
        ],
        'pick' => [
            'drinks' => [
                3 => [
                    'Pick who is most likely to text their ex tonight — they take a shot.', 'Pick the friend with the worst taste in partners — they take a shot and defend them.', 'Pick someone to read out their last sent text — or take a shot.',
                    'Pick who would last longest in a relationship with a stranger — they take a shot.', 'Pick the player most likely to fall asleep first tonight — they take a shot.', 'Pick someone to confess the most embarrassing nickname they ever had — or take a shot.',
                    'Pick who is the most competitive here — they take a shot and a bow.', 'Pick the player who gives the worst advice — they take a shot and give some.', 'Pick someone to take a shot and tell their worst fashion regret.',
                    'Pick who would survive a reality-TV villain edit — they take a shot and a villain pose.', 'Pick the friend who always knows the gossip — they take a shot and spill one (harmless).', 'Pick who is the worst at replying to messages — they take a shot and reply to one right now.',
                    '{target}, the wheel says shot! Then name your biggest dating red flag.',
                ],
            ],
            'family' => [
                3 => ['Pick someone to speak in a funny accent for the next three turns.', 'Pick a friend to do a dramatic slow-motion run across the room.', 'Pick someone to act like a robot until their next turn.', 'Pick the funniest person here — they tell a joke (the worse the better).', 'Pick someone to tell a story that starts with "It was a dark and stormy night…" in 20 seconds.', 'Pick a friend to do their best villain laugh.'],
                2 => ['Pick someone to pretend to be a waiter taking your order.', 'Pick a friend to hum a song — everyone guesses.'],
            ],
        ],
        'everyone' => [
            'drinks' => [
                3 => ['Everyone who has ghosted someone takes a shot.', 'Everyone who\'s been dumped by text takes a shot.', 'Everyone who has a crush on someone at this table takes a shot — nobody says who.', 'Everyone who has ever typed "lol" without laughing takes a shot.', 'Everyone who has lied about their relationship status takes a shot.', 'Everyone who has gone back to an ex takes a shot.', 'Everyone who is secretly checking their phone right now takes a shot.', 'Everyone who has been caught staring takes a shot.'],
            ],
            'family' => [
                3 => ['Everyone does a dramatic gasp on three.', 'Everyone shares one thing they\'re scared of — in a funny voice.', 'Everyone makes up a handshake with the person next to them.', 'Everyone stands up and does their best victory dance.', 'Everyone must talk like a pirate until the next round.'],
            ],
        ],
        'duel' => [
            'drinks' => [3 => ['The loser takes a shot and reads out their last sent message.', 'The loser takes a shot — the winner picks who joins them.', 'The loser takes a shot and confesses their worst date.', 'The loser takes a shot and a dare of the winner\'s choosing (clean).', 'The loser takes two sips and gives a toast.', 'The loser takes a shot and shows their most recent photo.']],
            'family' => [3 => ['The loser does the chicken dance while the winner counts to ten.', 'The loser must compliment the winner five times, each in a different voice.', 'The loser does an impression of the winner.', 'The loser sings a nursery rhyme as an opera singer.']],
        ],
        'rule' => [
            'drinks' => [3 => ['New rule: no first names — use "darling" instead. Slip up and take a sip.', 'New rule: everyone must end sentences with "if you know what I mean". Slip up and sip.', 'New rule: no laughing. If you laugh, you sip.', 'New rule: touch your nose before you speak. Forget and take a sip.', 'New rule: speak only in whispers. Slip up and take a sip.', 'New rule: everyone must raise a glass before each answer.', 'New rule: no pointing — use your elbow. Point and you sip.', 'New rule: when the roller says "cheers", everyone repeats it loudly or sips.']],
            'family' => [3 => ['New rule: everyone speaks in a funny voice. Slip up and do a star jump.', 'New rule: no saying "yes" or "no". Slip up and do a silly dance.', 'New rule: everybody must clap before they speak.', 'New rule: speak only in rhymes. Slip up and sing a line.', 'New rule: end every sentence with "my dear". Slip up and do a robot dance.']],
        ],
        'heart' => [
            'all' => [
                3 => ['Tell {other} what you honestly think your most attractive trait is.', 'Describe {other} as a movie character — and their love-interest.', 'Give {other} a pickup line that is actually good.', 'Tell {other} the best first date idea you\'ve ever heard.', 'Rate {other}\'s dancing out of ten and give one tip.'],
            ],
        ],
    ],

    'facts' => [
        [3, 'Hangovers get worse as you get older.', true, 'The body breaks down alcohol more slowly with age.', 2],
        [3, 'Drinking coffee sobers you up.', false, 'It only makes you feel more awake — it doesn\'t lower your blood alcohol.', 2],
        [3, 'Alcohol warms you up when it\'s cold.', false, 'It just makes you feel warm — it actually drops your core temperature.', 2],
        [3, 'The word "alcohol" comes from Arabic.', true, 'From "al-kuhl", a fine powder used for eye makeup.', 2],
        [3, 'Champagne bubbles make you get drunk faster.', true, 'Carbonation speeds up alcohol absorption.', 2],
        [3, 'Dogs can smell a person\'s fear.', false, 'They pick up on scent and body-language cues, not fear itself.', 2],
        [3, 'There is a world record for the longest kiss, and it lasted over 58 hours.', true, 'A Thai couple set it in 2013.', 2],
        [3, 'Tequila is made from cactus.', false, 'It\'s made from blue agave, which is a succulent — not a cactus.', 2],
        [3, 'Vodka was originally used as medicine.', true, 'Early vodka was used in Eastern Europe for medicinal purposes.', 2],
        [3, 'Humans share about 50% of their DNA with bananas.', true, 'It\'s a well-known fun fact (about half our genes have a banana equivalent).', 2],
        [3, 'Lightning never strikes the same place twice.', false, 'It often does — the Empire State Building is hit ~20 times a year.', 2],
        [3, 'A group of flamingos is called a flamboyance.', true, 'Yes, really.', 2],
        [3, 'Goldfish have a three-second memory.', false, 'They can remember things for months.', 2],
        [3, 'You swallow eight spiders a year in your sleep.', false, 'A myth with no evidence behind it.', 2],
        [3, 'Wine glasses are shaped to change how wine tastes.', true, 'The shape affects aroma and how the wine hits your palate.', 2],
        [3, 'Beer is older than the wheel.', true, 'Beer likely predates the wheel by several thousand years.', 2],
        [3, 'Whisky continues to age in the bottle.', false, 'It only ages in the barrel — once bottled it stops.', 2],
        [2, 'Honey never spoils.', true, 'Archaeologists found edible honey in ancient tombs.', 1],
        [2, 'Bananas grow on trees.', false, 'They grow on giant herbs — the "tree" is a stem of leaves.', 1],
        [2, 'Octopuses have three hearts.', true, 'Two pump blood to the gills, one to the body.', 1],
        [2, 'A day on Venus is longer than its year.', true, 'Venus rotates very slowly — one rotation takes longer than its orbit.', 1],
        [2, 'Humans only use 10% of their brains.', false, 'We use virtually all of it.', 1],
        [2, 'Sharks are older than trees.', true, 'Sharks have been around for over 400 million years.', 1],
        [2, 'The Great Wall of China is visible from space with the naked eye.', false, 'A famous myth — it isn\'t.', 1],
        [2, 'A shrimp\'s heart is in its head.', true, 'Most of a shrimp\'s organs are in its head.', 1],
        [1, 'Cows have best friends.', true, 'Cows form close bonds and get stressed apart.', 1],
        [1, 'The tongue is the strongest muscle in the body.', false, 'It\'s strong for its size, but the masseter and others are stronger.', 1],
        [1, 'Penguins can fly short distances.', false, 'They can\'t fly — they swim.', 1],
        [1, 'Ice is slippery because of pressure from your feet.', false, 'It\'s mostly a thin layer of water on the ice surface.', 1],
    ],

    'spotlight' => [
        [3, 'Pitch the worst possible dating-app bio — then read it out like a movie trailer.', 20], [3, 'Give a toast to someone who isn\'t here — make it either hilarious or touching.', 20],
        [3, 'Defend a terrible opinion for 15 seconds as if your life depends on it.', 20], [3, 'Act out a bad breakup in a famous movie scene — no words.', 20],
        [3, 'Sell {other} on a first date with you in 15 seconds.', 20], [3, 'Describe your ideal night out using only movie titles.', 25],
        [3, 'Give your best sports-commentator voice describing {other} eating a snack.', 20], [3, 'Sing a love song to an inanimate object in the room.', 20],
        [3, 'Tell the story of your worst first impression in three sentences.', 20], [3, 'Break up with the nearest object — dramatically.', 15],
        [2, 'Do a 15-second stand-up routine about {scene}.', 20], [2, 'Describe your best friend as a Netflix show.', 20], [2, 'Narrate someone\'s next move like a nature documentary.', 20],
        [2, 'Create a new cocktail (or mocktail) and name it after someone here.', 20], [2, 'Give a motivational speech to {other} about {topic}.', 20], [2, 'Rank your last three crushes as movie characters.', 20],
        [1, 'Say a tongue twister of the table\'s choice, twice.', 15], [1, 'Describe your day as a movie trailer.', 15], [1, 'Give the plot of your favourite movie in 10 words.', 15],
        [1, 'Pitch a new snack flavour — name it and sell it.', 15],
    ],

    'bluff' => [
        ['all', 3, 'Tell the table about the most embarrassing thing that has happened to you at a wedding. True or invented?'],
        ['all', 3, 'Describe your worst date ever. Real, or all in your head?'],
        ['all', 3, 'Tell us who your first kiss was with. Did it really happen like that?'],
        ['all', 3, 'Name the weirdest place you\'ve ever fallen asleep. True story?'],
        ['all', 3, 'Describe a secret you\'ve kept for years. Is it real?'],
        ['drinks', 3, 'Tell the story of the worst hangover of your life. Is it true?'],
        ['drinks', 3, 'Describe the most chaotic night out you\'ve had. Real or invented?'],
        ['family', 3, 'Describe the worst gift you ever received. True or tall tale?'],
        ['all', 2, 'Tell us about the strangest food you\'ve ever tried. Did you really eat it?'],
        ['all', 2, 'Describe a celebrity you\'ve met. Did it really happen?'],
        ['all', 2, 'Tell us about a time you got lost. Real or made up?'],
        ['all', 1, 'Describe your favourite childhood toy. Was it really yours?'],
        ['all', 1, 'Tell us about the best birthday you ever had. True story?'],
    ],
];
