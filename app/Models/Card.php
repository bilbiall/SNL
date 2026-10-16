<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    protected $fillable = ['board_id', 'kind', 'mode', 'intensity', 'text', 'answer', 'explain', 'penalty', 'timer', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'answer' => 'boolean'];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /** A random live card of this kind for the room's mode (or "all" modes), never wilder than the
     *  room's intensity. Falls back to ignoring intensity before giving up. */
    public static function draw(Room $room, string $kind, ?bool $random = null): ?self
    {
        $base = fn () => static::where('kind', $kind)
            ->whereIn('mode', ['all', $room->content_mode])
            ->where('is_active', true)
            // Pick-a-friend cards written with {target} are the wheel's deck; the rest are the roller's choice.
            ->when($kind === 'pick', fn ($q) => $q->where('text', $random ? 'like' : 'not like', '%{target}%'))
            ->where(fn ($q) => $q->whereNull('board_id')->orWhere('board_id', $room->board_id));

        return $base()->where('intensity', '<=', $room->intensity)->inRandomOrder()->first()
            ?? $base()->inRandomOrder()->first();
    }
}
