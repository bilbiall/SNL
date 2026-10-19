<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/** A key/value site setting, edited in GameSet → Settings. Secret values (API secrets, relay passwords) are encrypted at rest. */
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    private static ?array $loaded = null;

    /** @return array<string, ?string> every setting; an empty list (never an error) if the table does not exist yet */
    private static function loadAll(): array
    {
        if (self::$loaded === null) {
            try {
                self::$loaded = static::query()->pluck('value', 'key')->all();
            } catch (\Throwable) {
                self::$loaded = []; // the migration has not been run — the game must keep working
            }
        }

        return self::$loaded;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $v = self::loadAll()[$key] ?? null;

        return $v === null || $v === '' ? $default : $v;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$loaded = null;
    }

    public static function secret(string $key): ?string
    {
        $v = self::get($key);
        if ($v === null) {
            return null;
        }
        try {
            return Crypt::decryptString($v);
        } catch (DecryptException) {
            return null; // the app key changed: the secret has to be typed in again
        }
    }

    public static function putSecret(string $key, ?string $value): void
    {
        static::put($key, $value === null || $value === '' ? null : Crypt::encryptString($value));
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /** For tests: forget what was read earlier in this process. */
    public static function forgetLoaded(): void
    {
        self::$loaded = null;
    }
}
