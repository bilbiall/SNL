<?php

namespace Database\Seeders;

use App\Models\WordList;
use Illuminate\Database\Seeder;

/**
 * The slot words behind templated cards. "Recommend a movie for {scene}" becomes
 * "…a rainy Sunday" one time and "…a Nairobi traffic jam" the next — so the same square
 * never feels the same twice. Admins add hundreds more in GameSet → Words.
 *
 * Each entry: text, or [text, intensity] / [text, intensity, mode]. Default: intensity 1, mode "all".
 */
class WordListSeeder extends Seeder
{
    private function lists(): array
    {
        return [
            'scene' => [
                'a rainy Sunday', 'a Nairobi traffic jam', 'a first date', 'a road trip with friends', 'a Monday morning',
                'a group-chat argument', 'exam week', 'payday', 'a power outage', 'a long flight', 'a beach holiday',
                'a wedding reception', 'a 3am snack run', 'a surprise party', 'a job interview', 'a family reunion',
                'moving to a new city', 'a school reunion', 'a camping trip gone wrong', 'the last day of the month',
                ['getting ghosted', 2], ['a breakup', 2], ['an awkward run-in with your ex', 3],
                ['a situationship', 3, 'drinks'], ['a hangover brunch', 2, 'drinks'], ['a night out that got out of hand', 3, 'drinks'],
            ],
            'moment' => [
                'getting ready to go out', 'the drive home', 'a late-night study session', 'the last day of school',
                'a victory lap', 'cleaning the house', 'a celebration', 'waiting for a delayed bus', 'a lazy Saturday',
            ],
            'vibe' => [
                'main character energy', 'villain arc', 'soft life', 'chaotic good', '3am thoughts', 'gym motivation',
                'beach day', 'cosy rainy day', 'hot girl summer', 'road-trip singalong', 'confidence boost', ['heartbreak', 2],
            ],
            'hot_topic' => [
                'Pineapple belongs on pizza', 'Group chats should be illegal after 10pm', 'Voice notes are better than calls',
                'Breakfast is the best meal', 'Cereal is a soup', 'Dogs beat cats', 'Winter is better than summer',
                'Sunday is the worst day of the week', 'Ketchup belongs on eggs', 'Texting back after a day is rude',
                ['Splitting the bill evenly is unfair', 2], ['Your ex should never be a friend', 2], ['Reply-all should be a crime', 1],
                ['Dating apps are better than meeting people', 3, 'drinks'],
            ],
            'rando' => [
                'pineapple on pizza', 'group chats', 'karaoke', 'wedding speeches', 'Monday mornings', 'voice notes',
                'reality TV', 'the last text you sent', 'airport security', 'shared taxis', ['hangovers', 2, 'drinks'],
            ],
            'topic' => [
                'animals', 'space', 'food', 'Africa', 'the ocean', 'the human body', 'history', 'music', 'technology',
                'sports', 'the internet', 'movies', 'the weather', 'languages',
            ],
            'genre' => [
                'a horror', 'a rom-com', 'an action', 'a sci-fi', 'an animated', 'a thriller', 'a musical', 'a fantasy', 'a heist', 'a sitcom',
            ],
            'decade' => ['the 80s', 'the 90s', 'the 2000s', 'the 2010s'],
        ];
    }

    public function run(): void
    {
        // Genre words now include their article; drop the old bare ones.
        WordList::where('slot', 'genre')->where('text', 'not like', 'a %')->where('text', 'not like', 'an %')->delete();

        foreach ($this->lists() as $slot => $words) {
            foreach ($words as $w) {
                [$text, $intensity, $mode] = array_pad((array) $w, 3, null);
                WordList::firstOrCreate(
                    ['slot' => $slot, 'text' => $text],
                    ['intensity' => $intensity ?? 1, 'mode' => $mode ?? 'all', 'is_active' => true]
                );
            }
        }
    }
}
