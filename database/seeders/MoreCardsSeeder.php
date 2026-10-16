<?php

namespace Database\Seeders;

use App\Models\Card;
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
