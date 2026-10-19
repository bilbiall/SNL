<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A named (often sponsored) set of cards, switched on by date and mode. Managed in GameSet → Packs. */
class Pack extends Model
{
    protected $fillable = ['name', 'sponsor', 'tagline', 'logo', 'link', 'modes', 'starts_on', 'ends_on', 'is_active'];

    protected $casts = ['modes' => 'array', 'is_active' => 'boolean', 'starts_on' => 'date', 'ends_on' => 'date'];

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /** Switched on, and today is inside its dates. */
    public function scopeLive($q)
    {
        $today = now()->toDateString();

        return $q->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_on')->orWhere('starts_on', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_on')->orWhere('ends_on', '>=', $today));
    }

    /** Does this pack appear in the given content mode? (no mode list = all modes) */
    public function appliesTo(string $mode): bool
    {
        return empty($this->modes) || in_array($mode, $this->modes, true);
    }

    /** @return array<int, int> ids of the live packs that appear in this mode */
    public static function liveIdsFor(string $mode): array
    {
        return static::live()->get()->filter(fn (self $p) => $p->appliesTo($mode))->pluck('id')->all();
    }
}
