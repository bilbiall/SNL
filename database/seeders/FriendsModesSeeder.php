<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

/**
 * Content for the "boyz" and "girlies" modes — see database/seeders/data/friends-modes.php.
 * Safe to run repeatedly: it only adds what is missing, so cards edited or deleted in GameSet are never overwritten.
 */
class FriendsModesSeeder extends Seeder
{
    /** The data file in database/seeders/data (LoversModeSeeder points this at lovers-mode.php). */
    protected string $file = 'friends-modes.php';

    public function run(): void
    {
        $d = require __DIR__ . '/data/' . $this->file;
        $new = ['board_id' => null, 'is_active' => true];

        foreach ($d['cards'] as $kind => $modes) {
            foreach ($modes as $mode => $tiers) {
                foreach ($tiers as $intensity => $lines) {
                    foreach ($lines as $text) {
                        Card::firstOrCreate(['kind' => $kind, 'mode' => $mode, 'text' => $text], $new + ['intensity' => $intensity]);
                    }
                }
            }
        }

        foreach ($d['random_pick'] as $mode => $tiers) {
            foreach ($tiers as $intensity => $lines) {
                foreach ($lines as $text) {
                    Card::firstOrCreate(['kind' => 'pick', 'mode' => $mode, 'text' => $text], $new + ['intensity' => $intensity]);
                }
            }
        }

        foreach ($d['facts'] as $mode => $rows) {
            foreach ($rows as [$i, $text, $answer, $explain, $penalty]) {
                Card::firstOrCreate(['kind' => 'fact', 'mode' => $mode, 'text' => $text],
                    $new + ['intensity' => $i, 'answer' => $answer, 'explain' => $explain, 'penalty' => $penalty]);
            }
        }

        foreach (['spotlight', 'quickfire'] as $kind) {
            foreach ($d[$kind] as $mode => $rows) {
                foreach ($rows as [$i, $text, $secs]) {
                    Card::firstOrCreate(['kind' => $kind, 'mode' => $mode, 'text' => $text],
                        $new + ['intensity' => $i, 'timer' => $secs, 'penalty' => $i === 3 ? 2 : 1]);
                }
            }
        }

        foreach ($d['bluff'] as $mode => $rows) {
            foreach ($rows as [$i, $text]) {
                Card::firstOrCreate(['kind' => 'bluff', 'mode' => $mode, 'text' => $text], $new + ['intensity' => $i, 'penalty' => $i === 3 ? 2 : 1]);
            }
        }
    }
}
