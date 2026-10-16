<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Room extends Model
{
    protected $fillable = [
        'token', 'join_code', 'board_id', 'type', 'content_mode', 'intensity', 'status',
        'host_player_id', 'current_player_id', 'winner_player_id', 'turn_started_at', 'round',
        'max_players', 'active_rule', 'event_seq', 'actions', 'pending', 'pending_started_at', 'rematch_token', 'rematch_of',
    ];

    protected $casts = [
        'actions'            => 'array',
        'pending'            => 'array',
        'current_player_id' => 'integer',
        'host_player_id'    => 'integer',
        'winner_player_id'  => 'integer',
        'turn_started_at'    => 'datetime',
        'pending_started_at' => 'datetime',
    ];

    /** Entries kept in the replay log — plenty for a slow poller to catch up from. */
    public const LOG_KEEP = 24;

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('turn_order');
    }

    public function isParty(): bool
    {
        return $this->type === 'party';
    }

    public function isOnline(): bool
    {
        return $this->type === 'online';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /** Append one entry to the replay log and bump the sequence number. */
    public function log(array $entry): array
    {
        $this->event_seq = (int) $this->event_seq + 1;
        $entry = ['seq' => $this->event_seq] + $entry;
        $log = $this->actions ?? [];
        $log[] = $entry;
        $this->actions = array_slice($log, -self::LOG_KEEP);

        return $entry;
    }

    public static function newToken(): string
    {
        do {
            $t = Str::lower(Str::random(12));
        } while (static::where('token', $t)->exists());

        return $t;
    }

    public static function newJoinCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I — read aloud over a noisy table
        do {
            $c = '';
            for ($i = 0; $i < 5; $i++) {
                $c .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (static::where('join_code', $c)->exists());

        return $c;
    }
}
