<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    protected $fillable = ['slug', 'name', 'board_image', 'board_width', 'board_height', 'tile_count', 'is_active', 'sort_order', 'start_zone'];

    protected $casts = ['is_active' => 'boolean', 'start_zone' => 'array'];

    /** Boards players can pick from. */
    public static function playable()
    {
        return static::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    }

    public function imageUrl(): string
    {
        return $this->board_image ?: asset(config('snl.default_board_art'));
    }

    public function tiles(): HasMany
    {
        return $this->hasMany(Tile::class)->orderBy('number');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /** number => ['left','top','left_m','top_m'] — what the play page uses to place tokens. */
    public function positions(): array
    {
        $out = [];
        foreach ($this->tiles as $t) {
            $out[$t->number] = [
                'left'   => $t->pos_left ?? 50,
                'top'    => $t->pos_top ?? 50,
                'left_m' => $t->pos_left_mobile ?? $t->pos_left ?? 50,
                'top_m'  => $t->pos_top_mobile ?? $t->pos_top ?? 50,
            ];
        }
        // Square 0 = "before the start" — sits on the first square.
        $out[0] = $out[1] ?? ['left' => 8, 'top' => 90, 'left_m' => 8, 'top_m' => 90];
        // …unless GameSet set a start zone: then players wait spread out inside that rectangle.
        if (!empty($this->start_zone)) {
            $out[0]['zone'] = $this->start_zone;
        }

        return $out;
    }
}
