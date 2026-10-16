<?php

namespace App\Support;

/**
 * The retro pixel-art avatar roster: 16 looks (8 women, 8 men) × 7 skin shades.
 * An avatar key is "{look}-{skin}", e.g. "f3-4". Files live in public/avatars/ (see scripts/build-avatars.mjs).
 */
class Avatars
{
    public static function roster(): array
    {
        static $r = null;

        return $r ??= json_decode(file_get_contents(resource_path('avatars.json')), true);
    }

    public static function looks(): array
    {
        return self::roster()['looks'];
    }

    public static function skins(): array
    {
        return self::roster()['skins'];
    }

    public static function valid(?string $key): bool
    {
        if (!$key || !preg_match('/^([fm]\d+)-(\d+)$/', $key, $m)) {
            return false;
        }

        return in_array($m[1], array_column(self::looks(), 'id'), true)
            && in_array((int) $m[2], array_column(self::skins(), 'id'), true);
    }

    /** A varied default for seat $order — alternates women/men and walks through the skin shades. */
    public static function default(int $order): string
    {
        $women = array_values(array_filter(self::looks(), fn ($l) => $l['gender'] === 'f'));
        $men = array_values(array_filter(self::looks(), fn ($l) => $l['gender'] === 'm'));
        $pool = $order % 2 === 0 ? $men : $women;
        $look = $pool[intdiv($order, 2) % count($pool)]['id'];
        $skins = self::skins();

        return $look . '-' . $skins[($order * 3 + 1) % count($skins)]['id'];
    }

    public static function url(string $key): string
    {
        return asset("avatars/{$key}.svg");
    }
}
