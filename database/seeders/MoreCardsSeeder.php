<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\WordList;
use Illuminate\Database\Seeder;

/**
 * The expansion pack — see database/seeders/data/more-cards.php. Safe to run repeatedly: it only adds what's missing,
 * so cards an admin has edited or deleted by hand are never overwritten (apart from removing the duplicate-prone
 * original True-or-False facts that were re-worded).
 */
class MoreCardsSeeder extends Seeder
{
    public function run(): void
    {
        $d = require __DIR__ . '/data/more-cards.php';

        foreach ($d['cards'] as $kind => $modes) {
            foreach ($modes as $mode => $tiers) {
                foreach ($tiers as $intensity => $lines) {
                    foreach ($lines as $text) {
                        Card::firstOrCreate(['kind' => $kind, 'mode' => $mode === 'all' ? 'all' : $mode, 'text' => $text],
                            ['board_id' => null, 'intensity' => $intensity, 'is_active' => true]);
                    }
                }
            }
        }

        // Retired wording (the old "chaser" cards were confusing) — remove the originals so only the clearer ones are drawn.
        Card::whereIn('kind', ['pick', 'duel'])->where('text', 'like', '%chaser%')->delete();

        foreach ($d['random_pick'] ?? [] as $mode => $tiers) {
            foreach ($tiers as $intensity => $lines) {
                foreach ($lines as $text) {
                    Card::firstOrCreate(['kind' => 'pick', 'mode' => $mode, 'text' => $text],
                        ['board_id' => null, 'intensity' => $intensity, 'is_active' => true]);
                }
            }
        }

        // Free-square events (quickfire, group games, gifts, roll-again lines).
        $e = require __DIR__ . '/data/event-cards.php';
        foreach ($e['quickfire'] as [$i, $text, $secs]) {
            Card::firstOrCreate(['kind' => 'quickfire', 'mode' => 'all', 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'timer' => $secs, 'penalty' => $i === 3 ? 2 : 1, 'is_active' => true]);
        }
        foreach ($e['cards'] as $kind => $modes) {
            foreach ($modes as $mode => $tiers) {
                foreach ($tiers as $intensity => $lines) {
                    foreach ($lines as $text) {
                        Card::firstOrCreate(['kind' => $kind, 'mode' => $mode, 'text' => $text], ['board_id' => null, 'intensity' => $intensity, 'is_active' => true]);
                    }
                }
            }
        }

        // Content pack 2: bigger decks, templated quickfire / group games, word lists, spicier tails.
        $p = require __DIR__ . '/data/content-pack-2.php';
        foreach ($p['words'] as [$slot, $mode, $intensity, $text]) {
            WordList::firstOrCreate(['slot' => $slot, 'mode' => $mode, 'text' => $text], ['intensity' => $intensity, 'is_active' => true]);
        }
        foreach ($p['quickfire'] as [$i, $text, $secs]) {
            Card::firstOrCreate(['kind' => 'quickfire', 'mode' => 'all', 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'timer' => $secs, 'penalty' => $i === 3 ? 2 : 1, 'is_active' => true]);
        }
        foreach ($p['cards'] as $kind => $modes) {
            foreach ($modes as $mode => $tiers) {
                foreach ($tiers as $intensity => $lines) {
                    foreach ($lines as $text) {
                        Card::firstOrCreate(['kind' => $kind, 'mode' => $mode, 'text' => $text], ['board_id' => null, 'intensity' => $intensity, 'is_active' => true]);
                    }
                }
            }
        }
        foreach ($p['facts'] as [$i, $text, $answer, $explain, $penalty]) {
            Card::firstOrCreate(['kind' => 'fact', 'mode' => 'all', 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'answer' => $answer, 'explain' => $explain, 'penalty' => $penalty, 'is_active' => true]);
        }
        foreach ($p['spotlight'] as [$i, $text, $secs]) {
            Card::firstOrCreate(['kind' => 'spotlight', 'mode' => 'all', 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'timer' => $secs, 'penalty' => $i === 3 ? 2 : 1, 'is_active' => true]);
        }
        foreach ($p['bluff'] as [$mode, $i, $text]) {
            Card::firstOrCreate(['kind' => 'bluff', 'mode' => $mode, 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'penalty' => $i === 3 ? 2 : 1, 'is_active' => true]);
        }

        foreach ($d['facts'] as [$i, $text, $answer, $explain, $penalty]) {
            Card::firstOrCreate(['kind' => 'fact', 'mode' => 'all', 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'answer' => $answer, 'explain' => $explain, 'penalty' => $penalty, 'is_active' => true]);
        }

        foreach ($d['spotlight'] as [$i, $text, $secs]) {
            Card::firstOrCreate(['kind' => 'spotlight', 'mode' => 'all', 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'timer' => $secs, 'penalty' => $i === 3 ? 2 : 1, 'is_active' => true]);
        }

        foreach ($d['bluff'] as [$mode, $i, $text]) {
            Card::firstOrCreate(['kind' => 'bluff', 'mode' => $mode, 'text' => $text],
                ['board_id' => null, 'intensity' => $i, 'penalty' => $i === 3 ? 2 : 1, 'is_active' => true]);
        }
    }
}
