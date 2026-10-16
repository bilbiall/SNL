<?php

namespace App\Support;

/** The game-icon keys available in public/icons.svg (see resources/icons.json, `npm run icons`). */
class Icons
{
    /** @return list<string> */
    public static function keys(): array
    {
        static $keys = null;

        return $keys ??= array_values(array_filter(
            array_keys(json_decode(file_get_contents(resource_path('icons.json')), true) ?: []),
            fn ($k) => !str_starts_with($k, '_')
        ));
    }
}
