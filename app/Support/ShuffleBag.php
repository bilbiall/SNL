<?php

namespace App\Support;

use App\Models\Room;

/**
 * A per-game "shuffle bag". Instead of drawing at random with replacement (which repeats cards back to back),
 * a game works through a deck before any card comes round again, and the last few cards of a lap are held back
 * from the start of the next. State lives in rooms.deck_state: {used: {key: [ids…]}, events: [...], cool: n}.
 */
class ShuffleBag
{
    /**
     * @param array<int|string, int> $pool  id => intensity of every eligible entry
     * @return int|string|null  the chosen id (and records it as used)
     */
    public static function pick(Room $room, string $key, array $pool, int $intensity, float $exactBias = 0.65)
    {
        if (!$pool) {
            return null;
        }
        $state = $room->deck_state ?? [];
        $used = $state['used'][$key] ?? [];
        $fresh = array_values(array_diff(array_keys($pool), $used));
        if (!$fresh) { // lap finished — go again, but not straight back to the last few
            $used = array_slice($used, -min(4, intdiv(count($pool), 2)));
            $fresh = array_values(array_diff(array_keys($pool), $used));
            if (!$fresh) {
                $fresh = array_keys($pool);
                $used = [];
            }
        }
        // Lean towards cards written for this exact intensity (Spicy rooms mostly get Spicy cards).
        $exact = array_values(array_filter($fresh, fn ($id) => (int) $pool[$id] === $intensity));
        $from = ($exact && mt_rand() / mt_getrandmax() < $exactBias) ? $exact : $fresh;
        $id = $from[array_rand($from)];
        $used[] = $id;
        $state['used'][$key] = array_slice($used, -600);
        $room->deck_state = $state;

        return $id;
    }

    /** Free-square events: remembers the last two and a short cooldown so they never come in streaks. */
    public static function eventState(Room $room): array
    {
        $s = $room->deck_state ?? [];

        return ['events' => $s['events'] ?? [], 'cool' => (int) ($s['cool'] ?? 0)];
    }

    public static function saveEventState(Room $room, array $events, int $cool): void
    {
        $s = $room->deck_state ?? [];
        $s['events'] = array_slice($events, -3);
        $s['cool'] = $cool;
        $room->deck_state = $s;
    }
}
