<?php

namespace App\Models;

use App\Support\ShuffleBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    protected $fillable = ['board_id', 'pack_id', 'topic', 'kind', 'mode', 'intensity', 'text', 'answer', 'explain', 'penalty', 'timer', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'answer' => 'boolean'];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }

    /** The topics the table chose, as valid keys; an empty list means "Random" (everything). */
    public static function topicsOf(Room $room): array
    {
        $t = array_values(array_filter((array) ($room->deck_state['topics'] ?? []), fn ($k) => is_string($k)));
        if (!$t || in_array('random', $t, true)) {
            return [];
        }

        return array_values(array_intersect($t, array_keys((array) config('snl.topics'))));
    }

    /** The next card of this kind for the room's mode (or "all" modes), never wilder than the room's intensity.
     *  Drawn from a per-game shuffle bag: the whole deck is used before any card repeats, and Spicy rooms mostly get Spicy cards. */
    public static function draw(Room $room, string $kind, ?bool $random = null): ?self
    {
        $packs = Pack::liveIdsFor($room->content_mode); // sponsored / themed packs that are switched on today, for this mode
        $base = fn () => static::where('kind', $kind)
            ->whereIn('mode', ['all', $room->content_mode])
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('pack_id')->orWhereIn('pack_id', $packs))
            // Pick-a-friend cards written with {target} are the wheel's deck; the rest are the roller's choice.
            ->when($kind === 'pick', fn ($q) => $q->where('text', $random ? 'like' : 'not like', '%{target}%'))
            ->where(fn ($q) => $q->whereNull('board_id')->orWhere('board_id', $room->board_id));

        // Topics: when the table picked some (not "Random"), the question-style kinds only draw from those topics; if a topic has nothing
        // for this kind and mode, fall back to the general cards so a game never runs dry.
        $topics = static::topicsOf($room);
        $topical = $topics && in_array($kind, (array) config('snl.topic_kinds'), true);
        $tries = $topical ? [fn ($q) => $q->whereIn('topic', $topics), fn ($q) => $q->whereNull('topic')] : [fn ($q) => $q];
        $pool = [];
        foreach ($tries as $scope) {
            $pool = $scope($base())->where('intensity', '<=', $room->intensity)->pluck('intensity', 'id')->all()
                ?: $scope($base())->pluck('intensity', 'id')->all();
            if ($pool) {
                break;
            }
        }
        if (!$pool) {
            return null;
        }
        $id = ShuffleBag::pick($room, $kind . ($random ? '#r' : ''), $pool, (int) $room->intensity);

        return static::with('pack')->find($id);
    }
}
