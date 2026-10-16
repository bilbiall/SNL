<?php

/*
| The expansion pack: enough starter content that decks rarely repeat in a game.
|
|  cards    kind => mode => intensity (1 Chill · 2 Normal · 3 Spicy) => [lines]
|  facts    [intensity, statement, answer, explanation, penalty]      (True or false)
|  spotlight [intensity, prompt, seconds]
|  bluff    [mode, intensity, prompt]
|
| Text may use {you}, {other} and word-list slots ({scene}, {vibe}, {genre}, {topic}, {hot_topic}, {rando}, {moment}, {decade}).
| Everything here is also editable in GameSet → Cards.
*/
return [

    'cards' => [

        // ───────── You do it ─────────
        'self' => [
            'drinks' => [
                1 => [
                    'Sip if you have ever pretended to know a song\'s lyrics.',
                    'Sip if you have ever laughed at a joke you did not get.',
                    'Sip if you have a playlist for every mood.',
                    'Sip if you have ever replied "lol" without laughing.',
                    'Sip if you have forgotten someone\'s name right after meeting them.',
                    'Sip if you would rather rewatch a show than start a new one.',
                    'Sip if you have sung in the shower this week.',
                    'Take a sip and give the table a five-second dance move.',
                    'Sip if your phone battery is below 30% right now.',
                    'Sip if you have ever fallen asleep during a movie you picked.',
                ],
                2 => [
                    'Take a shot if you have ever texted "5 minutes away" from your couch.',
                    'Take a shot if you have ever liked an old photo by accident.',
                    'Take a shot if you have cried at an advert.',
                    'Take a shot if you pulled an all-nighter this month.',
                    'Take a shot and reveal your most-used emoji.',
                    'Take a shot if you have called food for one "meal prep".',
                    'Take a shot if you have fake-laughed in the last hour.',
                    'Take a shot if you changed your outfit three times today.',
                    'Take a shot if you have unread messages in more than 20 chats.',
                    'Take a shot if you have ever gone back for your phone mid-date.',
                ],
                3 => [
                    'Take a shot if you have ever pretended to be sick to skip plans.',
                    'Take a shot if you have ever left a group chat dramatically.',
                    'Take a shot if you have drunk-texted someone you should not have.',
                    'Take a shot if you have ever had a crush on a friend\'s sibling.',
                    'Take a shot if you have lied about your age.',
                    'Take a shot if you have matched with someone you know on a dating app.',
                    'Take a shot if you have gone on a date just for the free food.',
                    'Take a shot and show your most recent search — or take another.',
                ],
            ],
            'family' => [
                1 => [
                    'Make up a new handshake and teach it to {other}.',
                    'Talk like a pirate until your next turn.',
                    'Name five animals that start with the same letter in ten seconds.',
                    'Pretend to be a statue until the next player rolls.',
                    'Hum a song — the table guesses it.',
                    'Do your best superhero landing pose.',
                    'Say your own name backwards.',
                ],
                2 => [
                    'Give yourself a dramatic movie-star introduction.',
                    'Spin around three times, then name a country.',
                    'Do an impression of a sleepy sloth.',
                    'Whisper everything you say until your next turn.',
                    'Tell your best joke in ten seconds.',
                    'Strike a yoga pose and hold it for ten seconds.',
                    'Do the chicken dance.',
                    'Describe your day using only three words.',
                ],
                3 => [
                    'Sing "Happy Birthday" like an opera singer.',
                    'Do the robot dance for eight seconds.',
                    'Walk like a cat to the other side of the table and back.',
                    'Make the funniest face you can and hold it for five seconds.',
                    'Give {other} a made-up superhero name — and a catchphrase.',
                ],
            ],
        ],

        // ───────── Pick a friend ─────────
        'pick' => [
            'drinks' => [
                1 => [
                    'Pick who has the best laugh — they take a sip.',
                    'Pick the best dancer here — they sip and show a move.',
                    'Pick who would win a cooking contest — they take a sip.',
                    'Pick the friend who is always on time — they take a sip.',
                    'Pick who gives the best advice — they take a sip.',
                    'Pick someone to name their go-to karaoke song, then sip.',
                ],
                2 => [
                    'Pick who is most likely to become famous — they take a shot.',
                    'Pick who would last longest on a desert island — they take a shot.',
                    'Pick the friend who always chooses the restaurant — they take a shot.',
                    'Pick who is most likely to forget a birthday — they take a shot.',
                    'Pick the group\'s official photographer — they take a shot.',
                    'Pick someone to take a shot and tell their worst date story.',
                    'Pick who spends the most on takeout — they take a shot.',
                    'Pick the biggest drama starter — they take a shot.',
                ],
                3 => [
                    'Pick who is most likely to date two people at once — they take a shot.',
                    'Pick who has the best flirting game — shot, then a pickup line.',
                    'Pick who is most likely to get lost on a night out — they take a shot.',
                    'Pick the friend who ghosts the most — shot, then they explain.',
                    'Pick who has the most embarrassing playlist — shot, then play a song from it.',
                    'Pick who is most likely to cry at a wedding — they take a shot.',
                    'Pick the player who would survive a horror movie least — they take a shot.',
                    'Pick someone to take a shot and confess a guilty pleasure.',
                ],
            ],
            'family' => [
                1 => [
                    'Pick someone to sing the first line of their favourite song.',
                    'Pick a friend to tell a joke.',
                    'Pick someone to do five jumping jacks.',
                    'Pick the funniest person here — they tell a joke.',
                    'Pick someone to tell about the best day of their life in one sentence.',
                ],
                2 => [
                    'Choose who has to say a tongue twister three times fast.',
                    'Pick a friend to make an animal sound of your choice.',
                    'Pick someone to describe their dream holiday in ten seconds.',
                    'Pick a friend to pretend to be a news anchor for ten seconds.',
                    'Pick someone to teach everyone a dance move.',
                    'Pick a friend to balance on one foot for ten seconds.',
                    'Choose a friend to give a speech about their favourite snack.',
                ],
                3 => [
                    'Pick someone to act out a movie scene — we guess!',
                    'Pick someone to say the alphabet backwards, as far as they can.',
                    'Pick a friend to talk in a silly voice until their next turn.',
                    'Pick someone to draw a doodle in the air — we guess!',
                ],
            ],
        ],

        // ───────── Everyone ─────────
        'everyone' => [
            'drinks' => [
                1 => [
                    'Everyone who has sung in the car takes a sip.',
                    'Everyone wearing socks takes a sip.',
                    'Everyone who has used a meme in a chat today takes a sip.',
                    'Everyone who loves pizza takes a sip.',
                    'Everyone with a pet takes a sip.',
                    'Everyone who is a morning person takes a sip.',
                ],
                2 => [
                    'Everyone who has sent a message to the wrong chat takes a sip.',
                    'Everyone who has cried at a movie takes a sip.',
                    'Everyone who has slept through an alarm takes a sip.',
                    'Everyone with a cracked phone screen takes a sip.',
                    'Everyone who has binged a whole season in a weekend takes a sip.',
                    'Waterfall! Everyone drinks until the player on their right stops.',
                ],
                3 => [
                    'Everyone with a situationship story takes a shot.',
                    'Everyone who has drunk-texted an ex takes a shot.',
                    'Everyone who has ever been ghosted takes a shot.',
                    'Everyone with a crush in this room takes a shot — and nobody says who.',
                    'Everyone takes a shot. Whoever finishes last takes another.',
                ],
            ],
            'family' => [
                1 => [
                    'Everyone does their best dinosaur roar.',
                    'Everyone shares their favourite colour in one word.',
                    'Everyone gets up and does five star jumps.',
                    'Everyone says "cheese!" and takes a pretend photo.',
                    'Everyone names a favourite movie in five seconds.',
                ],
                2 => [
                    'Everyone hums the same song — the roller guesses it.',
                    'Everyone pretends to be a different animal for five seconds.',
                    'Everyone claps a rhythm — copy the roller.',
                    'Everyone swaps seats with the person on their left.',
                    'Everyone says one thing they are proud of this week.',
                    'Everyone does a slow-motion high five.',
                ],
                3 => [
                    'Everyone does a silent scream (mouth only).',
                    'Everyone holds their breath for five seconds — last to breathe wins a cheer.',
                    'Everyone points at the person they think is funniest — on three!',
                ],
            ],
        ],

        // ───────── Duel — the loser does it ─────────
        'duel' => [
            'drinks' => [
                1 => ['The loser takes a sip and compliments the winner.', 'The loser sips and sings a line of any song.'],
                2 => ['The loser takes a shot and picks the next song.', 'The loser takes a shot and does a victory dance for the winner.', 'The loser takes a shot — the winner chooses a toast for them to say first.'],
                3 => ['The loser takes a shot and confesses an embarrassing moment.', 'The loser takes a shot — the winner asks one question, answered honestly or another shot.'],
            ],
            'family' => [
                1 => ['The loser does a silly face for ten seconds.', 'The loser gives the winner a high five and a bow.'],
                2 => ['The loser hops on one leg while saying the alphabet.', 'The loser tells the funniest joke they know.', 'The loser does five jumping jacks.'],
                3 => ['The loser acts like a chicken for ten seconds.', 'The loser must sing the chorus of any song, loudly.'],
            ],
        ],

        // ───────── New rule ─────────
        'rule' => [
            'drinks' => [
                1 => [
                    'New rule: clink glasses before every sip.',
                    'New rule: whoever says "literally" takes a sip.',
                    'New rule: when someone laughs at their own joke, they sip.',
                    'New rule: no swearing. Slip up, take a sip.',
                ],
                2 => [
                    'New rule: use "Boss" instead of anyone\'s name. Slip up, take a sip.',
                    'New rule: end every sentence with "in this economy". Slip up, take a sip.',
                    'New rule: nobody says "I" — say "this person". Slip up, take a sip.',
                    'New rule: say "bestie" before every sentence. Slip up, take a sip.',
                    'New rule: drink only with your non-dominant hand.',
                ],
                3 => [
                    'New rule: speak in a British accent. Slip up, take a sip.',
                    'New rule: no touching your face. Touch it, take a sip.',
                    'New rule: whenever someone rolls a six, everyone takes a sip.',
                    'New rule: compliment someone before you drink. Forget, take another.',
                ],
            ],
            'family' => [
                1 => [
                    'New rule: say "please" before every sentence. Slip up, do a star jump.',
                    'New rule: clap once before you speak.',
                    'New rule: speak slowly like a sloth until the next rule.',
                ],
                2 => [
                    'New rule: use "Captain" instead of anyone\'s name. Slip up, do a silly pose.',
                    'New rule: everyone talks in a funny accent. Slip up, do a dance move.',
                    'New rule: nobody may say "I". Slip up, do a star jump.',
                    'New rule: end every sentence with "meow". Slip up, do a cat stretch.',
                ],
                3 => [
                    'New rule: stand up whenever you speak. Forget, do three jumping jacks.',
                    'New rule: speak only in rhymes. Slip up, do a chicken dance.',
                    'New rule: no pointing, no laughing. Break it, sing a line.',
                ],
            ],
        ],

        // ───────── Swap places — flavour for the roller's choice ─────────
        'swap' => [
            'all' => [
                1 => ['Swap places! Plot twist.', 'Positions reversed — enjoy the new view.', 'Fancy a trade? Choose your swap partner.', 'Body swap! (On the board only.)'],
                2 => ['The universe says switch — choose who you trade places with.', 'Fate has other plans. Pick a player to swap with.', 'A twist nobody saw coming — swap with someone!'],
                3 => ['Chaos card: swap with anyone, no mercy.', 'Steal their spot! Choose who you swap with.', 'Switcheroo! Somebody is about to get very annoyed.'],
            ],
        ],

        // ───────── Free squares — a breather, not an action ─────────
        'neutral' => [
            'all' => [
                1 => [
                    'Free square. Breathe.', 'Safe space — nothing happens.', 'A moment of peace before the chaos.', 'Coast mode: activated.',
                    'No dares here — just vibes.', 'Even legends get a rest square.', 'Safe… for now.', 'Nothing to see here. Move along.',
                    'The board is giving you a break.', 'Neutral territory. Stretch a little.', 'Peace treaty in effect.',
                    'Phone check allowed — ten seconds.', 'Free square. Pick a song for the playlist.', 'You found the snack square. (Imaginary snacks only.)',
                    'Quiet square. Soak it in.', 'Nothing happens. Suspicious…',
                ],
            ],
            'drinks' => [
                1 => ['Water break — hydrate before the next round.', 'Pace yourself: a glass of water is a power move.', 'Hydration check! Water is your friend.'],
            ],
            'family' => [
                1 => ['Take a deep breath… and a big smile.', 'Wiggle your fingers and shake it out!'],
            ],
        ],
    ],

    // ───────── The wheel picks (kind = pick, written with {target}) ─────────
    'random_pick' => [
        'drinks' => [
            1 => ['The wheel picked {target}! Take a sip and give the table a compliment.', '{target} was chosen — take a sip and show your best dance move.', '{target}, the wheel says sip! Then name your go-to karaoke song.'],
            2 => ['The wheel picked {target}! Take a shot and tell your worst date story.', '{target} was chosen — take a shot, then pick the next song.', 'Fate chose {target}: take a shot and confess a guilty pleasure.', '{target} takes a shot — the wheel has spoken!'],
            3 => ['The wheel is merciless: {target} takes a shot and answers one honest question.', '{target} was chosen — take a shot and reveal your most-used emoji.', 'Fate chose {target}: take a shot and read your last text out loud (or take another).'],
        ],
        'family' => [
            1 => ['The wheel picked {target}! Tell everyone your favourite joke.', '{target} was chosen — do your best victory dance.', 'The wheel chose {target}: name three animals in five seconds.'],
            2 => ['The wheel picked {target}! Do an impression of your favourite cartoon character.', '{target} was chosen — sing the first line of any song.', 'Fate chose {target}: tell a funny story from school or work.'],
            3 => ['The wheel is wild: {target}, do ten seconds of your best robot dance.', '{target} was chosen — talk in a silly voice until your next turn.', 'Fate chose {target}: act out an animal and let the table guess!'],
        ],
    ],

    // ───────── True or false ─────────
    'facts' => [
        // true
        [1, 'A group of owls is called a parliament.', true, 'Yes — and a group of crows is a murder.', 1],
        [1, 'Strawberries have their seeds on the outside.', true, 'Each "seed" is actually a tiny fruit called an achene.', 1],
        [1, 'Starfish don\'t have brains.', true, 'They have a nerve ring and nerves in their arms instead.', 1],
        [1, 'Sound cannot travel through space.', true, 'There is no air to carry the vibrations.', 1],
        [1, 'Kenya gained its independence in 1963.', true, 'On 12 December 1963.', 1],
        [1, 'A newborn kangaroo is about the size of a jellybean.', true, 'It then crawls into its mother\'s pouch to grow.', 1],
        [1, 'Sea otters hold hands when they sleep.', true, 'So they don\'t drift apart.', 1],
        [1, 'The Pacific Ocean is larger than all of Earth\'s land put together.', true, 'It covers about a third of the planet.', 1],
        [2, 'Octopuses have blue blood.', true, 'It carries copper-based hemocyanin instead of iron-based haemoglobin.', 1],
        [2, 'Bananas are slightly radioactive.', true, 'They contain potassium-40 — harmlessly.', 1],
        [2, 'A day on Mars is slightly longer than a day on Earth.', true, 'About 24 hours and 37 minutes.', 1],
        [2, 'Dolphins call each other by name.', true, 'Each has a signature whistle that others use to address it.', 1],
        [2, 'Crocodiles cannot stick their tongues out.', true, 'Their tongues are attached to the roof of their mouths.', 1],
        [2, 'Venus is the hottest planet in our solar system.', true, 'Even though Mercury is closer to the Sun, Venus\'s thick atmosphere traps heat.', 1],
        [2, 'An ostrich\'s eye is bigger than its brain.', true, 'About 5 cm across.', 1],
        [2, 'Elephants cannot jump.', true, 'Their legs just aren\'t built to leave the ground.', 1],
        [2, 'Cashews grow on the outside of a fruit.', true, 'The nut hangs from the bottom of the cashew apple.', 1],
        [3, 'The Eiffel Tower was originally meant to be temporary.', true, 'It was built for the 1889 World\'s Fair and meant to stand 20 years.', 2],
        [3, 'Russia is larger in surface area than Pluto.', true, 'Russia: ~17 million km². Pluto: ~16.7 million km².', 2],
        [3, 'The word "robot" comes from a Czech word for forced labour.', true, 'From "robota", coined in a 1920 play.', 2],
        [3, 'Cats have fewer toes on their back paws than on their front paws.', true, 'Five in front, four behind.', 2],
        [3, 'Ravens can mimic human speech.', true, 'Some can copy words and even voices.', 2],
        // false
        [1, 'Humans have only five senses.', false, 'We also sense balance, temperature, pain and more.', 1],
        [1, 'Penguins live at the North Pole.', false, 'They live in the Southern Hemisphere.', 1],
        [1, 'The capital of Australia is Sydney.', false, 'It\'s Canberra.', 1],
        [1, 'Mombasa is the capital of Kenya.', false, 'It\'s Nairobi.', 1],
        [1, 'Zanzibar is part of Kenya.', false, 'It\'s part of Tanzania.', 1],
        [1, 'Gen Z is the generation before Millennials.', false, 'Gen Z comes after Millennials.', 1],
        [2, 'Cats always land on their feet.', false, 'They usually do, but not always — it depends on height and time.', 1],
        [2, 'Different parts of your tongue taste different flavours.', false, 'The "tongue map" is a myth — all areas detect all tastes.', 1],
        [2, 'Vikings wore horned helmets.', false, 'There\'s no evidence they did.', 1],
        [2, 'Fortune cookies come from China.', false, 'They were popularised in California, with roots in Japan.', 1],
        [2, 'You lose most of your body heat through your head.', false, 'You lose heat evenly from any uncovered body part.', 1],
        [2, 'Camels store water in their humps.', false, 'Humps store fat.', 1],
        [2, 'Kenya\'s highest mountain is Kilimanjaro.', false, 'It\'s Mount Kenya — Kilimanjaro is in Tanzania.', 1],
        [2, 'Einstein failed maths at school.', false, 'He excelled at it.', 1],
        [3, 'Spinach has more iron than red meat.', false, 'A decimal-point error in the 1800s started that myth.', 2],
        [3, 'Humans evolved from chimpanzees.', false, 'We share a common ancestor with them, millions of years ago.', 2],
        [3, 'Glass is a slow-moving liquid.', false, 'Old windows are uneven because of how glass was made, not because it flows.', 2],
        [3, 'Microwaves cook food from the inside out.', false, 'They heat the outer layers first.', 2],
        [3, 'Water drains in opposite directions in each hemisphere because of the Coriolis effect.', false, 'The effect is far too weak to matter in a sink.', 2],
        [3, 'Hair and nails keep growing after death.', false, 'The skin shrinks back, making them look longer.', 2],
        [3, 'Sharks cannot get cancer.', false, 'They can — it\'s just rarer than the myth suggests.', 2],
    ],

    // ───────── Spotlight (creative prompts) ─────────
    'spotlight' => [
        [1, 'Name a movie that always makes you cry — and the exact scene.', 20],
        [1, 'Recommend a song to play when {scene} hits.', 15],
        [1, 'Name three movies with "love" in the title. Fifteen seconds!', 15],
        [1, 'What is your comfort movie for {scene}? Defend it.', 15],
        [1, 'Recite the best opening line of any song you know.', 15],
        [1, 'Recommend a podcast, a snack and a blanket for {scene}.', 20],
        [1, 'Recommend a movie to the player on your left, based on how they look right now.', 15],
        [2, 'Hum a movie theme — the table guesses it.', 20],
        [2, 'Pitch the sequel nobody asked for — to a movie you love.', 20],
        [2, 'Recommend a K-drama, anime or series to {other} and say why.', 20],
        [2, 'Name five songs by the same artist in fifteen seconds.', 15],
        [2, 'Mime a movie — the table guesses it. No words!', 20],
        [2, 'Give {rando} a one-star or five-star review, food-critic style.', 15],
        [2, 'Sell {other} a product that doesn\'t exist — fifteen seconds.', 15],
        [2, 'Say "red lorry, yellow lorry" three times fast.', 15],
        [2, 'Describe {scene} as a movie genre and give it a title.', 15],
        [2, 'Give the friend on your right a new nickname and explain it.', 15],
        [2, 'Name a song that is a red flag if someone plays it during {scene}.', 15],
        [2, 'Which movie or series character are you today? Justify it.', 15],
        [3, 'Make up a love song about {rando} — sing the chorus.', 20],
        [3, 'Recommend a movie for the worst date ever. Why?', 15],
        [3, 'Rank everyone here as movie characters: hero, sidekick, villain, extra.', 25],
        [3, 'Confess your guilty-pleasure song. Sing a line.', 20],
        [3, 'Give your best "it\'s giving…" about {other}.', 15],
        [3, 'Deliver a fifteen-second breakup speech to {rando}.', 15],
        [3, 'Plan the perfect movie night with {genre} movie: snack, seating and who sits where.', 25],
        [3, 'Recommend three songs for {moment} and explain the vibe of each.', 25],
        [3, 'Pitch a Netflix show about {scene}. Title and one-line plot.', 20],
        [3, 'Narrate {other}\'s next ten seconds, nature-documentary style.', 15],
        [3, 'Explain the plot of {genre} movie using only emojis… described out loud.', 20],
    ],

    // ───────── Fact or bluff — the roller\'s claim ─────────
    'bluff' => [
        ['all', 1, 'Name a famous person who shares your birthday month. Real or invented?'],
        ['all', 1, 'Describe the weirdest food you have ever eaten. Is it real?'],
        ['all', 1, 'Tell the table about a place you have visited. Did you really go?'],
        ['all', 1, 'State a fun fact about {topic} using exact numbers.'],
        ['all', 1, 'Share your first-ever memory. Real or made up?'],
        ['all', 2, 'Tell us the best compliment you have ever received. True or tall tale?'],
        ['all', 2, 'Describe your very first job. Real or invented?'],
        ['all', 2, 'Share your most embarrassing moment during {scene}. Did it happen?'],
        ['all', 2, 'Tell a "fact" about {other}\'s past. It could be true!'],
        ['all', 2, 'State a surprising world record. True or made up?'],
        ['all', 3, 'Tell us your wildest dating story. Real or invented?'],
        ['all', 3, 'Confess the biggest lie you have ever told. Did you really tell it?'],
        ['all', 3, 'Name the most expensive thing you have ever broken. Real?'],
        ['all', 3, 'Share a secret talent nobody here knows about. Real?'],
        ['drinks', 3, 'Describe your worst drunk decision. True story?'],
        ['family', 3, 'Describe your worst holiday disaster. True story?'],
    ],
];
