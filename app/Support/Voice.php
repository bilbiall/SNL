<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Voice chat for online rooms. An admin picks the provider in GameSet → Settings:
 *   off      no microphone button
 *   p2p      free: phones talk to each other directly (WebRTC), the server only passes the "hello" messages around
 *   livekit  a hosted voice service (LiveKit Cloud or your own LiveKit server); the server only signs a join token
 * A mode only counts as on when it is fully set up, so a half-filled form never shows a button that cannot work.
 */
class Voice
{
    public const DEFAULT_STUN = "stun:stun.l.google.com:19302\nstun:stun1.l.google.com:19302";
    public const DEFAULT_SDK = 'https://cdn.jsdelivr.net/npm/livekit-client@2.5.0/dist/livekit-client.umd.js';

    public static function mode(): string
    {
        $m = (string) Setting::get('voice.mode', 'off');
        if ($m === 'livekit' && self::livekit() === null) {
            return 'off';
        }

        return in_array($m, ['p2p', 'livekit'], true) ? $m : 'off';
    }

    public static function maxP2p(): int
    {
        return max(2, min(12, (int) Setting::get('voice.max_p2p', 6)));
    }

    /** @return array{url: string, key: string, secret: string, sdk: string}|null */
    public static function livekit(): ?array
    {
        $url = trim((string) Setting::get('voice.lk_url', ''));
        $key = trim((string) Setting::get('voice.lk_key', ''));
        $secret = (string) Setting::secret('voice.lk_secret');
        if ($url === '' || $key === '' || $secret === '') {
            return null;
        }
        $url = preg_replace('#^https://#i', 'wss://', preg_replace('#^http://#i', 'ws://', $url));

        return ['url' => rtrim($url, '/'), 'key' => $key, 'secret' => $secret, 'sdk' => (string) Setting::get('voice.lk_sdk', self::DEFAULT_SDK)];
    }

    /** The STUN / TURN servers a browser uses to find a path between two phones. */
    public static function iceServers(): array
    {
        $stun = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) Setting::get('voice.stun', self::DEFAULT_STUN)))));
        $out = $stun ? [['urls' => $stun]] : [];
        $turn = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) Setting::get('voice.turn_urls', '')))));
        if ($turn) {
            $out[] = ['urls' => $turn, 'username' => (string) Setting::get('voice.turn_user', ''), 'credential' => (string) Setting::secret('voice.turn_pass')];
        }

        return $out;
    }

    /** What the play page needs to know (never any secret). */
    public static function clientConfig(): array
    {
        $mode = self::mode();

        return ['mode' => $mode, 'max' => $mode === 'p2p' ? self::maxP2p() : 12];
    }

    /** A LiveKit access token (a signed JWT) letting one player join one room's audio. Valid for two hours. */
    public static function livekitToken(array $lk, string $room, string $identity, string $name): string
    {
        $b64 = fn (string $s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
        $now = time();
        $head = $b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = $b64(json_encode([
            'iss' => $lk['key'], 'sub' => $identity, 'name' => $name, 'jti' => $identity . '-' . $now,
            'nbf' => $now - 10, 'exp' => $now + 7200,
            'video' => ['room' => $room, 'roomJoin' => true, 'canPublish' => true, 'canSubscribe' => true, 'canPublishData' => false],
        ]));
        $sig = $b64(hash_hmac('sha256', "$head.$body", $lk['secret'], true));

        return "$head.$body.$sig";
    }
}
