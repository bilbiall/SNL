<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

/** "Most likely to…" vote cards — see database/seeders/data/vote-cards.php. Safe to run repeatedly: only adds what is missing. */
class VoteCardsSeeder extends Seeder
{
    public function run(): void
    {
        $d = require __DIR__ . '/data/vote-cards.php';

        foreach ($d as $mode => $tiers) {
            foreach ($tiers as $intensity => $lines) {
                foreach ($lines as $text) {
                    Card::firstOrCreate(['kind' => 'vote', 'mode' => $mode, 'text' => $text],
                        ['board_id' => null, 'intensity' => $intensity, 'penalty' => $intensity === 3 ? 2 : 1, 'is_active' => true]);
                }
            }
        }
    }
}
