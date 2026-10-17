<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A word that fills a {slot} in a card's text, so one card reads differently every time. */
class WordList extends Model
{
    protected $fillable = ['slot', 'mode', 'intensity', 'text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** The next word for this slot, suited to the room's mode and never wilder than its intensity — from the game's shuffle bag, so words don't repeat soon. */
    public static function pick(Room $room, string $slot): ?string
    {
        $base = fn () => static::where('slot', $slot)->where('is_active', true)->whereIn('mode', ['all', $room->content_mode]);
        $pool = $base()->where('intensity', '<=', $room->intensity)->pluck('intensity', 'id')->all() ?: $base()->pluck('intensity', 'id')->all();
        if (!$pool) {
            return null;
        }

        return static::whereKey(\App\Support\ShuffleBag::pick($room, 'w:' . $slot, $pool, (int) $room->intensity, 0.5))->value('text');
    }
}
