<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A word that fills a {slot} in a card's text, so one card reads differently every time. */
class WordList extends Model
{
    protected $fillable = ['slot', 'mode', 'intensity', 'text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** A random live word for this slot, suited to the room's mode and never wilder than its intensity. */
    public static function pick(Room $room, string $slot): ?string
    {
        $base = fn () => static::where('slot', $slot)->where('is_active', true)
            ->whereIn('mode', ['all', $room->content_mode]);

        return $base()->where('intensity', '<=', $room->intensity)->inRandomOrder()->value('text')
            ?? $base()->inRandomOrder()->value('text');
    }
}
