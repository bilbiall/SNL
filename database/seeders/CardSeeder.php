<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

/**
 * Starter decks. Admins grow all of this in GameSet → Cards (and Words).
 *
 * Mode:      drinks | family | all (works in every mode — used for facts, prompts and anything with no drink in it)
 * Intensity: 1 Chill · 2 Normal · 3 Spicy  (a room only draws cards up to the level it was set to)
 * Text may use {placeholders}: {you}, {other} = players at the table; any other {slot} is a word list (Words page).
 * `penalty` (1|2) = how many units a wrong answer / lost bluff / failed Spotlight costs (units → sips/shots or forfeits by mode & intensity).
 */
class CardSeeder extends Seeder
{
    /** [intensity, text, answer(true|false), explanation, penalty] */
    private function facts(): array
    {
        return [
            // ── true ──
            [1, 'Octopuses have three hearts.', true, 'Two pump blood through the gills, one around the body.', 1],
            [1, 'Honey never spoils.', true, 'Edible honey has been found in ancient Egyptian tombs.', 1],
            [1, 'Bananas are berries, but strawberries are not.', true, 'Botanically, a berry grows from one flower with one ovary — strawberries don\'t qualify.', 1],
            [1, 'A group of flamingos is called a flamboyance.', true, 'Yes. Really.', 1],
            [1, 'Butterflies taste with their feet.', true, 'Receptors on their feet tell them if a leaf is good to eat.', 1],
            [1, 'Mount Kilimanjaro is the highest mountain in Africa.', true, 'At 5,895 metres, in Tanzania.', 1],
            [1, 'Lake Victoria is the largest lake in Africa by area.', true, 'And one of the biggest freshwater lakes in the world.', 1],
            [1, 'Giraffes have the same number of neck bones as humans: seven.', true, 'Theirs are just enormously long.', 1],
            [1, 'The Great Rift Valley runs through Kenya.', true, 'It stretches thousands of kilometres across East Africa.', 1],
            [1, 'Gen Z is the generation that comes after Millennials.', true, 'Roughly those born from the late 1990s to early 2010s.', 1],
            [1, 'Coffee beans are actually seeds.', true, 'They come from inside the coffee cherry.', 1],
            [2, 'A day on Venus is longer than its year.', true, 'It spins once every 243 Earth days but orbits the Sun in 225.', 1],
            [2, 'Cleopatra lived closer in time to the Moon landing than to the building of the Great Pyramid.', true, 'The pyramid is ~2,500 years older than she was.', 1],
            [2, 'Wombat poop is cube-shaped.', true, 'It stops it rolling away — handy for marking territory.', 1],
            [2, 'The shortest war in history lasted about 38 minutes.', true, 'Britain vs Zanzibar, 1896.', 1],
            [2, 'Hawaiian pizza was invented in Canada.', true, 'Sam Panopoulos, 1962.', 1],
            [2, 'Lightning can be hotter than the surface of the Sun.', true, 'Around five times hotter.', 1],
            [2, 'Sharks are older than trees.', true, 'Sharks: 400+ million years. Trees: ~350 million.', 1],
            [2, 'Cows have best friends and get stressed when they are separated.', true, 'Studies show lower heart rates when paired with a favourite.', 1],
            [2, 'A shrimp\'s heart is in its head.', true, 'Right behind its brain, in the thorax region.', 1],
            [2, 'Mount Kenya is the second-highest mountain in Africa.', true, 'Only Kilimanjaro is taller.', 1],
            [2, 'Dolphins sleep with half of their brain awake.', true, 'So they can keep surfacing to breathe.', 1],
            [2, 'Tomatoes are botanically a fruit.', true, 'Because they grow from a flower and hold seeds.', 1],
            [3, 'Scotland\'s national animal is the unicorn.', true, 'Officially, since the 1300s.', 2],
            [3, 'Sloths can hold their breath longer than dolphins.', true, 'Up to around 40 minutes by slowing their heart rate.', 2],
            [3, 'Oxford University is older than the Aztec Empire.', true, 'Teaching at Oxford began around 1096; Tenochtitlan was founded in 1325.', 2],
            [3, 'There are more trees on Earth than stars in the Milky Way.', true, 'About 3 trillion trees vs roughly 100–400 billion stars.', 2],
            [3, 'Bees can recognise human faces.', true, 'Research shows they can learn and remember them.', 2],
            [3, 'The stapes, in your ear, is the smallest bone in the human body.', true, 'About 3 millimetres long.', 2],
            [3, 'The Eiffel Tower can be about 15 cm taller in summer.', true, 'Heat makes the iron expand.', 2],
            // ── false ──
            [1, 'Wi-Fi stands for "Wireless Fidelity".', false, 'It doesn\'t stand for anything — it was just a catchy brand name.', 1],
            [1, 'Goldfish have a three-second memory.', false, 'They can remember things for months.', 1],
            [1, 'Nairobi is the capital of Tanzania.', false, 'Nairobi is Kenya\'s capital. Tanzania\'s official capital is Dodoma.', 1],
            [1, 'Pineapples grow on trees.', false, 'They grow from the centre of a low, spiky plant.', 1],
            [1, 'The Great Wall of China can be seen from space with the naked eye.', false, 'It\'s long, but far too narrow to spot from orbit.', 1],
            [1, 'Mount Kilimanjaro is in Kenya.', false, 'It\'s in Tanzania — you can just see it from Kenya on a clear day.', 1],
            [1, 'Humans only use 10% of their brains.', false, 'We use pretty much all of it.', 1],
            [2, 'Lightning never strikes the same place twice.', false, 'The Empire State Building is hit around 20 times a year.', 1],
            [2, 'Bulls charge because they hate the colour red.', false, 'Bulls are red-green colour-blind — they react to the movement.', 1],
            [2, 'The Sahara is the largest desert in the world.', false, 'Antarctica is. The Sahara is the largest hot desert.', 1],
            [2, 'Peanuts are true nuts.', false, 'They\'re legumes, like beans and lentils.', 1],
            [2, 'Swahili is only spoken in Kenya.', false, 'Also Tanzania, Uganda, DR Congo and beyond.', 1],
            [2, 'Kenya is the largest country in Africa by area.', false, 'Algeria is. Kenya ranks somewhere around 22nd in Africa.', 1],
            [2, 'Sugar makes kids hyperactive.', false, 'Study after study finds no link.', 1],
            [2, 'The Nile flows from south to north.', true, 'It flows north into the Mediterranean — which is why Upper Egypt is in the south.', 1],
            [3, 'You swallow around eight spiders a year in your sleep.', false, 'A made-up statistic that went viral.', 2],
            [3, 'Mount Everest is the tallest mountain when measured from base to peak.', false, 'Mauna Kea, in Hawaii, is — most of it is underwater.', 2],
            [3, 'Napoleon was unusually short.', false, 'About 5\'7" — average for the time. French inches were longer.', 2],
            [3, 'Chameleons change colour mainly to blend in.', false, 'Mostly mood, temperature and signalling to other chameleons.', 2],
            [3, 'Bats are blind.', false, 'They can see — and many also use echolocation.', 2],
            [3, 'Dogs only see in black and white.', false, 'They see blues and yellows, just not reds and greens.', 2],
            [3, 'The Earth is closest to the Sun during the northern summer.', false, 'It\'s closest in early January.', 2],
            [3, 'Cats should drink milk every day.', false, 'Most adult cats are lactose intolerant.', 2],
        ];
    }

    /** [kind, mode, intensity, text, extra[]] */
    private function cards(): array
    {
        $rows = [];
        $add = function (string $kind, string $mode, int $i, string $text, array $x = []) use (&$rows) {
            $rows[] = [$kind, $mode, $i, $text, $x];
        };

        // ═════ Fact or bluff — the roller makes the claim ═════
        foreach ([
            [1, 'Tell the table a fun fact about {topic}. True, or totally made up? Say it with a straight face.'],
            [1, 'Give a "fact" about {topic}. Everyone else decides if you\'re bluffing.'],
            [1, 'Describe your most useless talent. Is it real? Only you know.'],
            [2, 'State a record, number or date about {topic}. Make it sound official.'],
            [2, 'Tell a "true story" from your life about {scene}. Real or invented — you won\'t say.'],
            [2, 'Make a claim about {other}. It could be true, it could be total chaos.'],
            [3, 'Confess something you "never told anyone". Real or invented? Keep your poker face.'],
            [3, 'Tell the most unbelievable story you have about {scene}. Is it true?'],
        ] as [$i, $t]) {
            $add('bluff', 'all', $i, $t, ['penalty' => $i === 3 ? 2 : 1]);
        }

        // ═════ Spotlight — creative prompts on the clock ═════
        foreach ([
            [1, 'Recommend a movie for {scene}. Sell it in one sentence.', 15],
            [1, 'Name a song that is the soundtrack to {scene}.', 15],
            [1, 'Pitch {genre} movie set in {scene}. Title and one-line plot — go!', 20],
            [1, 'Name three songs for a "{vibe}" playlist. No repeats.', 15],
            [1, 'Recommend a series to binge during {scene}.', 15],
            [1, 'Recommend a game, a song and a snack for {moment}.', 20],
            [1, 'Name five artists from {decade}. Clock\'s ticking.', 15],
            [1, 'Start a song with a lyric that fits "{vibe}" — the table finishes it.', 15],
            [2, 'Cast {other} in {genre} film. Who do they play, and why?', 15],
            [2, 'Describe {scene} using only song titles or movie titles.', 20],
            [2, 'Hot take: "{hot_topic}". Agree or disagree and defend it.', 20],
            [2, 'Rate {rando} out of 10 and justify it in one line. No cap.', 15],
            [2, 'Use "no cap", "it\'s giving" and "main character energy" in one sentence about {scene}.', 20],
            [2, 'What is the perfect snack for {scene}? Defend your choice.', 15],
            [2, 'Describe your day so far as a movie-trailer voiceover.', 20],
            [2, 'Give a movie recommendation for {other}\'s current mood, and say what that mood is.', 20],
            [3, 'Cast your friends: who is the villain, who is the sidekick, and who dies first in {genre} movie?', 20],
            [3, 'Invent a Gen Z slang word and define it. The table decides if it sticks.', 20],
            [3, 'Roast {other} like a stand-up comedian. Kind-ish.', 20],
            [3, 'Recommend a movie for {other} that is secretly a message. They must guess the message.', 25],
        ] as [$i, $t, $sec]) {
            $add('spotlight', 'all', $i, $t, ['timer' => $sec, 'penalty' => $i === 3 ? 2 : 1]);
        }

        // ═════ You do it ═════
        foreach ([
            [1, 'drinks', 'Take a sip.'],
            [1, 'drinks', 'Take a sip — and say something nice about the person on your left.'],
            [1, 'drinks', 'Sip if you have ever left someone on read.'],
            [1, 'drinks', 'Sip if you have ever said "on my way" while still in bed.'],
            [1, 'drinks', 'Sip, then tell the table your most useless talent.'],
            [2, 'drinks', 'Take a shot.'],
            [2, 'drinks', 'Take a shot if your screen time is over six hours a day.'],
            [2, 'drinks', 'Take a shot, then swap seats with the person on your right.'],
            [2, 'drinks', 'Take a shot and do your best impression of {other}.'],
            [3, 'drinks', 'Take a shot if you have ever sent a text to the wrong person — then tell the story.'],
            [3, 'drinks', 'Take a shot and read your last sent message out loud.'],
            [3, 'drinks', 'Take a shot if you have ever stalked an ex\'s page.'],
            [1, 'family', 'Do your best victory dance!'],
            [1, 'family', 'Say the alphabet backwards, as far as you can.'],
            [1, 'family', 'Do an impression of {other} until your next turn.'],
            [2, 'family', 'Sing the chorus of any song — everyone has to guess it.'],
            [2, 'family', 'Do ten star jumps while the table counts out loud.'],
            [2, 'family', 'Tell a joke. If nobody laughs, do it again!'],
            [3, 'family', 'Speak in a robot voice until your next turn.'],
            [3, 'family', 'Balance a spoon on your nose for five seconds.'],
            [3, 'family', 'Do a silly walk across the room and back.'],
        ] as [$i, $m, $t]) {
            $add('self', $m, $i, $t);
        }

        // ═════ Pick a friend ═════
        foreach ([
            [1, 'drinks', 'Pick a friend to take a sip.'],
            [1, 'drinks', 'Pick who is most likely to cancel plans last minute — they take a sip.'],
            [1, 'drinks', 'Choose a friend: they sip, and tell us their favourite song right now.'],
            [2, 'drinks', 'Choose a friend to take a shot.'],
            [2, 'drinks', 'Pick who is most likely to ghost someone. They take a shot.'],
            [2, 'drinks', 'Pick someone to take a shot — then you choose a toast for them to say.'],
            [3, 'drinks', 'Pick the player with the worst taste in music. They take a shot and defend it.'],
            [3, 'drinks', 'Pick someone: they take a shot and tell a secret.'],
            [1, 'family', 'Pick a friend to give you a compliment.'],
            [1, 'family', 'Choose someone to tell the table their favourite food.'],
            [2, 'family', 'Choose a friend to do ten star jumps.'],
            [2, 'family', 'Pick someone to act out an animal — we guess!'],
            [3, 'family', 'Pick someone to speak in a funny accent for the next round.'],
            [3, 'family', 'Pick a friend to walk like a catwalk model.'],
        ] as [$i, $m, $t]) {
            $add('pick', $m, $i, $t);
        }

        // ═════ Everyone ═════
        foreach ([
            [1, 'drinks', 'Everybody take a sip.'],
            [1, 'drinks', 'Everyone who has muted a group chat takes a sip.'],
            [1, 'drinks', 'Cheers! Everyone clinks and sips.'],
            [2, 'drinks', 'Everyone who has sent a risky text at 2am takes a sip.'],
            [2, 'drinks', 'Everyone sips, then compliments the player on their left.'],
            [3, 'drinks', 'Phones out: whoever has the lowest battery takes a shot.'],
            [3, 'drinks', 'Everyone takes a shot. Cheers!'],
            [1, 'family', 'Everybody cheer as loud as you can!'],
            [1, 'family', 'Everyone high-fives the person next to them.'],
            [2, 'family', 'Everyone does a little dance for ten seconds.'],
            [2, 'family', 'Everybody says what they are most thankful for today.'],
            [3, 'family', 'Everybody swap seats with someone else — fast!'],
            [3, 'family', 'Everyone does their best monkey impression.'],
        ] as [$i, $m, $t]) {
            $add('everyone', $m, $i, $t);
        }

        // ═════ Duel — the loser does it ═════
        foreach ([
            [1, 'drinks', 'The loser takes a sip.'],
            [2, 'drinks', 'The loser takes a shot.'],
            [3, 'drinks', 'The loser takes a shot and tells a secret.'],
            [1, 'family', 'The loser does a silly dance.'],
            [2, 'family', 'The loser does five star jumps.'],
            [3, 'family', 'The loser does a funny walk across the room.'],
        ] as [$i, $m, $t]) {
            $add('duel', $m, $i, $t);
        }

        // ═════ New rule ═════
        foreach ([
            [1, 'drinks', 'New rule: no pointing — whoever points takes a sip.'],
            [1, 'drinks', 'New rule: everyone says "cheers" before they sip.'],
            [2, 'drinks', 'New rule: speak like a movie-trailer narrator. Slip up, take a sip.'],
            [2, 'drinks', 'New rule: end every sentence with "no cap". Slip up, take a sip.'],
            [2, 'drinks', 'New rule: phones face-down. First to touch theirs takes a sip.'],
            [3, 'drinks', 'New rule: speak only in questions. Slip up, take a sip.'],
            [3, 'drinks', 'New rule: no laughing! Whoever laughs takes a sip.'],
            [1, 'family', 'New rule: no pointing — whoever points does a silly pose.'],
            [2, 'family', 'New rule: speak like a movie-trailer narrator. Slip up, do a star jump.'],
            [2, 'family', 'New rule: nobody may say "yes" or "no". Slip up, do a silly pose.'],
            [3, 'family', 'New rule: speak only in questions. Slip up, do a chicken dance.'],
        ] as [$i, $m, $t]) {
            $add('rule', $m, $i, $t);
        }

        return $rows;
    }

    public function run(): void
    {
        // True-or-False replaced the old read-aloud facts — those had no answer to check.
        Card::where('kind', 'fact')->whereNull('answer')->delete();
        // Reworded so the genre word carries its own article ("a horror", "an action").
        Card::whereIn('text', [
            'Pitch a {genre} movie set in {scene}. Title and one-line plot — go!',
            'Cast {other} in a {genre} film. Who do they play, and why?',
            'Cast your friends: who is the villain, who is the sidekick, and who dies first in a {genre} movie?',
        ])->delete();

        foreach ($this->facts() as [$i, $text, $answer, $explain, $penalty]) {
            Card::updateOrCreate(
                ['kind' => 'fact', 'mode' => 'all', 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'answer' => $answer, 'explain' => $explain, 'penalty' => $penalty, 'is_active' => true]
            );
        }

        foreach ($this->cards() as [$kind, $mode, $i, $text, $extra]) {
            Card::firstOrCreate(
                ['kind' => $kind, 'mode' => $mode, 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'is_active' => true] + $extra
            );
        }
    }
}
