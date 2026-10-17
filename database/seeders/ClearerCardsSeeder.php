<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

/**
 * Rewords vague cards and adds relatable "if you have ever …" ones — see database/seeders/data/clearer-cards.php.
 * Safe to run repeatedly: old wording is only replaced when it still matches exactly, and new cards are only added when missing.
 */
class ClearerCardsSeeder extends Seeder
{
    public function run(): void
    {
        $d = require __DIR__ . '/data/clearer-cards.php';

        foreach ($d['replace'] as $old => $new) {
            Card::where('text', $old)->update(['text' => $new]);
        }

        foreach ($d['cards'] as [$intensity, $text]) {
            Card::firstOrCreate(
                ['kind' => 'self', 'mode' => 'drinks', 'text' => $text],
                ['board_id' => null, 'intensity' => $intensity, 'penalty' => 1, 'is_active' => true]
            );
        }
    }
}
