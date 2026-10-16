<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    /**
     * The starter decks. [kind][mode] => [intensity => [lines]]
     * Intensity: 1 chill · 2 normal · 3 wild. Admins edit/extend all of this in GameSet → Cards.
     * Any drink in any card can be swapped for water — the setup screen says so.
     */
    private function decks(): array
    {
        return [
            'self' => [
                'drinks' => [
                    1 => ['Take a sip.', 'Take a sip — and say something nice about the person on your left.', 'Sip, then tell the table your most useless talent.'],
                    2 => ['Take a shot.', 'Take a shot and do your best impression of someone at the table.', 'Take a shot, then swap seats with the person on your right.'],
                    3 => ['Take a shot — no complaining.', 'Take a shot and confess your most embarrassing moment of the year.', 'Take a shot and name the song you have played most this year.'],
                ],
                'family' => [
                    1 => ['Do your best victory dance!', 'Say the alphabet backwards, as far as you can.', 'Do your best animal impression until the next player rolls.'],
                    2 => ['Sing the chorus of any song — everyone has to guess it.', 'Do ten star jumps while the table counts out loud.', 'Tell a joke. If nobody laughs, do it again!'],
                    3 => ['Speak in a robot voice until your next turn.', 'Balance a spoon on your nose for five seconds.', 'Do a silly walk across the room and back.'],
                ],
            ],
            'pick' => [
                'drinks' => [
                    1 => ['Pick a friend to take a sip.', 'Pick someone to take a sip with you.', 'Choose a friend: they sip, and tell us their favourite song.'],
                    2 => ['Choose a friend to take a shot.', 'Pick someone to take a shot — you pick their chaser.', 'Pick a friend: they take a shot, you take a sip.'],
                    3 => ['Choose a friend to take a shot and give a toast.', 'Pick someone: they take a shot and tell a secret.', 'Pick two sips for a friend — and a dare of your choice.'],
                ],
                'family' => [
                    1 => ['Pick a friend to give you a compliment.', 'Choose someone to tell the table their favourite food.', 'Pick a friend to do a happy dance.'],
                    2 => ['Choose a friend to do ten star jumps.', 'Pick someone to sing a line of a song of your choice.', 'Pick a friend to act out an animal — we guess!'],
                    3 => ['Choose a friend to speak in a funny accent for the next round.', 'Pick someone to do their best catwalk.', 'Pick a friend to balance something on their head until their next turn.'],
                ],
            ],
            'everyone' => [
                'drinks' => [
                    1 => ['Everybody take a sip.', 'Cheers! Everyone clinks and takes a sip.', 'Everybody who is wearing blue takes a sip.'],
                    2 => ['Everyone takes a sip — the roller takes two.', 'Everybody who has ever been late to a party sips.', 'Everyone sips, then compliments the player on their left.'],
                    3 => ['Waterfall! Everyone drinks until the player on their right stops.', 'Everyone takes a shot. Cheers!', 'Everybody who has a phone on the table takes a sip.'],
                ],
                'family' => [
                    1 => ['Everybody cheer as loud as you can!', 'Everyone high-fives the person next to them.', 'Everybody strike a pose and hold it for five seconds.'],
                    2 => ['Everyone does a little dance for ten seconds.', 'Everybody says what they are most thankful for today.', 'Everyone whisper-shouts "snakes and ladders!" together.'],
                    3 => ['Everybody swap seats with someone else, fast!', 'Everyone does their best monkey impression.', 'Everybody freezes — last to freeze does a forfeit of the table\'s choice.'],
                ],
            ],
            'duel' => [
                'drinks' => [
                    1 => ['The loser takes a sip.', 'Loser sips and cheers the winner.'],
                    2 => ['The loser takes a shot.', 'Loser takes a shot, winner picks the toast.'],
                    3 => ['The loser takes a shot and tells a secret.', 'Loser takes a shot — and the winner dares them to something silly.'],
                ],
                'family' => [
                    1 => ['The loser does a silly dance.', 'Loser gives the winner a compliment.'],
                    2 => ['The loser does five star jumps.', 'Loser sings the first line of a song.'],
                    3 => ['The loser does a funny walk across the room.', 'Loser acts like a chicken until their next turn.'],
                ],
            ],
            'fact' => [
                'drinks' => [
                    1 => $facts = [
                        'Fun fact: honey never spoils — edible honey has been found in ancient Egyptian tombs.',
                        'Fun fact: octopuses have three hearts.',
                        'Fun fact: bananas are berries, but strawberries are not.',
                        'Fun fact: a group of flamingos is called a flamboyance.',
                        'Fun fact: butterflies taste with their feet.',
                        'Fun fact: Mount Kilimanjaro, at 5,895 m, is the highest mountain in Africa.',
                        'Fun fact: Lake Victoria is the largest lake in Africa by area.',
                        'Fun fact: a day on Venus is longer than its year.',
                        'Fun fact: giraffes have the same number of neck bones as humans — seven.',
                        'Fun fact: Cleopatra lived closer in time to the Moon landing than to the building of the Great Pyramid.',
                        'Fun fact: wombat poop is cube-shaped.',
                        'Fun fact: the shortest war in history, between Britain and Zanzibar in 1896, lasted about 38 minutes.',
                        'Fun fact: coffee beans are really the seeds of a fruit called the coffee cherry.',
                        'Fun fact: Hawaiian pizza was invented in Canada.',
                        'Fun fact: lightning can be hotter than the surface of the Sun.',
                        'Fun fact: sharks are older than trees.',
                        'Fun fact: the Great Rift Valley runs right through Kenya.',
                        'Fun fact: cows have best friends and get stressed when they are separated.',
                    ],
                ],
                'family' => [
                    1 => $facts,
                ],
            ],
            'rule' => [
                'drinks' => [
                    1 => ['New rule: no pointing — whoever points takes a sip.', 'New rule: everyone says "cheers" before they sip.', 'New rule: no first names — call everyone "Captain". Slip up, take a sip.'],
                    2 => ['New rule: drink with your left hand only. Slip up, take a sip.', 'New rule: nobody may say the word "drink". Slip up, take a sip.', 'New rule: phones face-down. First to touch theirs takes a sip.'],
                    3 => ['New rule: end every sentence with "my friend". Slip up, take a sip.', 'New rule: no laughing! Whoever laughs takes a sip.', 'New rule: speak only in questions. Slip up, take a sip.'],
                ],
                'family' => [
                    1 => ['New rule: no pointing — whoever points does a silly pose.', 'New rule: everyone says "please" before speaking.', 'New rule: call everyone "Captain" — slip up and do a star jump.'],
                    2 => ['New rule: nobody may say "yes" or "no". Slip up, do a silly pose.', 'New rule: use only your left hand to roll. Slip up, do a little dance.', 'New rule: phones face-down. First to touch theirs sings a line.'],
                    3 => ['New rule: end every sentence with "my friend". Slip up, do five star jumps.', 'New rule: no laughing! Whoever laughs gives a compliment.', 'New rule: speak only in questions. Slip up, do a chicken dance.'],
                ],
            ],
        ];
    }

    public function run(): void
    {
        foreach ($this->decks() as $kind => $modes) {
            foreach ($modes as $mode => $byIntensity) {
                foreach ($byIntensity as $intensity => $lines) {
                    foreach ($lines as $text) {
                        Card::firstOrCreate(
                            ['kind' => $kind, 'mode' => $mode, 'text' => $text],
                            ['board_id' => null, 'intensity' => $intensity, 'is_active' => true]
                        );
                    }
                }
            }
        }
    }
}
