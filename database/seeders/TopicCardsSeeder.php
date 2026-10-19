<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

/**
 * The topic packs (football, movies, music, world, history, did-you-know, science, Kenya, sports, tech, food) —
 * see database/seeders/data/topic-cards.php. Safe to run repeatedly: only adds what is missing, so edits made in GameSet are never overwritten.
 * A topic card that is already in the game as a general card (same kind, same text, all modes) is not added twice: the existing card is
 * simply tagged with the topic, so a table on "Random" never sees the same question twice.
 */
class TopicCardsSeeder extends Seeder
{
    public function run(): void
    {
        $d = require __DIR__ . '/data/topic-cards.php';

        foreach ($d as $topic => $sets) {
            foreach ($sets['facts'] ?? [] as [$i, $text, $answer, $explain, $penalty]) {
                $this->put($topic, 'fact', $text, ['intensity' => $i, 'answer' => $answer, 'explain' => $explain, 'penalty' => $penalty]);
            }
            foreach ($sets['quickfire'] ?? [] as [$i, $text, $secs]) {
                $this->put($topic, 'quickfire', $text, ['intensity' => $i, 'timer' => $secs, 'penalty' => $i === 3 ? 2 : 1]);
            }
            foreach ($sets['spotlight'] ?? [] as [$i, $text, $secs]) {
                $this->put($topic, 'spotlight', $text, ['intensity' => $i, 'timer' => $secs, 'penalty' => $i === 3 ? 2 : 1]);
            }
            foreach ($sets['groupgame'] ?? [] as [$i, $text]) {
                $this->put($topic, 'groupgame', $text, ['intensity' => $i]);
            }
            foreach ($sets['bluff'] ?? [] as [$i, $text]) {
                $this->put($topic, 'bluff', $text, ['intensity' => $i, 'penalty' => $i === 3 ? 2 : 1]);
            }
        }
    }

    private function put(string $topic, string $kind, string $text, array $extra): void
    {
        $general = Card::where('kind', $kind)->where('text', $text)->whereNull('topic')->where('mode', 'all')->first();
        if ($general) {
            $general->update(['topic' => $topic]); // already in the game: just file it under the topic
            return;
        }
        Card::firstOrCreate(['kind' => $kind, 'mode' => 'all', 'topic' => $topic, 'text' => $text], ['board_id' => null, 'is_active' => true] + $extra);
    }
}
