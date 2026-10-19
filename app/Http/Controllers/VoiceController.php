<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Room;
use App\Support\Voice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Voice chat for online rooms (see App\Support\Voice). Only seated, still-playing members of the room may use it.
 *   LiveKit:  token()  → a signed join token for this room's audio
 *   Free p2p: join() / poll() / signal() / leave() → the server only passes small "hello" messages (offers, answers, ICE candidates)
 *             between phones; the audio itself goes phone to phone. State lives in the cache, short-lived, never in the database.
 */
class VoiceController extends Controller
{
    private const FRESH = 15;       // seconds a phone may stay quiet before it counts as gone
    private const INBOX_MAX = 120;  // queued messages per phone

    public function token(Request $request, Room $room): JsonResponse
    {
        $me = $this->seated($request, $room);
        $lk = Voice::livekit();
        if (!$me || Voice::mode() !== 'livekit' || !$lk) {
            return $this->no($me ? 'Voice chat is not switched on.' : 'You are not seated at this table.');
        }

        return response()->json([
            'url' => $lk['url'], 'sdk' => $lk['sdk'],
            'token' => Voice::livekitToken($lk, 'snl-' . $room->token, (string) $me->id, $me->name),
        ]);
    }

    public function join(Request $request, Room $room): JsonResponse
    {
        $me = $this->seated($request, $room);
        if (!$me || Voice::mode() !== 'p2p') {
            return $this->no($me ? 'Voice chat is not switched on.' : 'You are not seated at this table.');
        }
        $full = false;
        $peers = $this->locked($room, function () use ($room, $me, &$full) {
            $peers = $this->freshPeers($room);
            if (!isset($peers[$me->id]) && count($peers) >= Voice::maxP2p()) {
                $full = true;

                return $peers;
            }
            $peers[$me->id] = time();
            Cache::put($this->k($room, 'peers'), $peers, 600);
            Cache::forget($this->k($room, 'in', $me->id)); // start with an empty inbox

            return $peers;
        });
        if ($full) {
            return $this->no('Voice is full (' . Voice::maxP2p() . ' people). You can keep playing.');
        }

        return response()->json(['me' => $me->id, 'peers' => array_map('intval', array_keys($peers)), 'ice' => Voice::iceServers()]);
    }

    /** Heartbeat + who is in voice + any messages for me (read once). */
    public function poll(Request $request, Room $room): JsonResponse
    {
        $me = $this->seated($request, $room);
        if (!$me || Voice::mode() !== 'p2p') {
            return $this->no('Voice chat is not available.');
        }
        $out = $this->locked($room, function () use ($room, $me) {
            $peers = $this->freshPeers($room);
            if (!isset($peers[$me->id])) {
                return null; // timed out: the phone must join again
            }
            $peers[$me->id] = time();
            Cache::put($this->k($room, 'peers'), $peers, 600);
            $msgs = Cache::pull($this->k($room, 'in', $me->id), []);

            return ['peers' => array_map('intval', array_keys($peers)), 'messages' => $msgs];
        });

        return $out === null ? $this->no('Reconnecting voice…', 410) : response()->json($out);
    }

    public function signal(Request $request, Room $room): JsonResponse
    {
        $me = $this->seated($request, $room);
        if (!$me || Voice::mode() !== 'p2p') {
            return $this->no('Voice chat is not available.');
        }
        $d = $request->validate(['to' => 'required|integer', 'kind' => 'required|in:offer,answer,ice', 'data' => 'required']);
        if (strlen(json_encode($d['data'])) > 8000) {
            return $this->no('Message too large.');
        }
        $ok = $this->locked($room, function () use ($room, $me, $d) {
            $peers = $this->freshPeers($room);
            if (!isset($peers[$me->id], $peers[$d['to']]) || (int) $d['to'] === $me->id) {
                return false;
            }
            $key = $this->k($room, 'in', (int) $d['to']);
            $box = Cache::get($key, []);
            $box[] = ['from' => $me->id, 'kind' => $d['kind'], 'data' => $d['data']];
            Cache::put($key, array_slice($box, -self::INBOX_MAX), 120);

            return true;
        });

        return response()->json(['ok' => $ok]);
    }

    public function leave(Request $request, Room $room): JsonResponse
    {
        $me = $this->seated($request, $room);
        if ($me) {
            $this->locked($room, function () use ($room, $me) {
                $peers = $this->freshPeers($room);
                unset($peers[$me->id]);
                Cache::put($this->k($room, 'peers'), $peers, 600);
                Cache::forget($this->k($room, 'in', $me->id));
            });
        }

        return response()->json(['ok' => true]);
    }

    // ───────────────────────────── helpers ─────────────────────────────

    /** The signed-in player of this online room (the TV and strangers have no seat). */
    private function seated(Request $request, Room $room): ?Player
    {
        if (!$room->isOnline() || $room->status === 'completed') {
            return null;
        }
        $id = $request->session()->get("snl.me.{$room->id}");
        $p = $id ? Player::where('id', $id)->where('room_id', $room->id)->first() : null;

        return $p && $p->status === 'active' ? $p : null;
    }

    /** @return array<int, int> player id => last seen, only those seen recently */
    private function freshPeers(Room $room): array
    {
        $now = time();

        return array_filter((array) Cache::get($this->k($room, 'peers'), []), fn ($t) => $now - (int) $t <= self::FRESH);
    }

    private function k(Room $room, string $part, ?int $pid = null): string
    {
        return "snl_voice_{$room->id}_{$part}" . ($pid !== null ? "_{$pid}" : '');
    }

    private function locked(Room $room, callable $fn): mixed
    {
        return Cache::lock("snl_voice_lock_{$room->id}", 5)->block(3, $fn);
    }

    private function no(string $msg, int $status = 422): JsonResponse
    {
        return response()->json(['error' => $msg], $status);
    }
}
