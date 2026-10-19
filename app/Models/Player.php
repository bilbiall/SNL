<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    protected $fillable = [
        'room_id', 'name', 'color', 'avatar', 'turn_order', 'team', 'position', 'status',
        'powers', 'stats', 'mission', 'missed_turns', 'prev_player_id',
    ];

    protected $casts = ['powers' => 'array', 'stats' => 'array', 'mission' => 'array'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function power(string $key): int
    {
        return (int) (($this->powers ?? [])[$key] ?? 0);
    }

    public function addPower(string $key, int $delta): void
    {
        $max = (int) config("snl.powers.$key.max", 3);
        $p = $this->powers ?? [];
        $p[$key] = max(0, min($max, (int) ($p[$key] ?? 0) + $delta));
        $this->powers = $p;
    }

    public function bump(string $stat, int $by = 1): void
    {
        $s = $this->stats ?? [];
        $s[$stat] = (int) ($s[$stat] ?? 0) + $by;
        $this->stats = $s;
    }

    public function stat(string $key): int
    {
        return (int) (($this->stats ?? [])[$key] ?? 0);
    }
}
