<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Player;
use App\Models\Room;
use App\Services\GameEngine;
use App\Services\GameException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Everything a player does at the table. There are no accounts: party mode is one
 * shared device (the room link is the key), online mode remembers "who am I in this
 * room" in the visitor's session.
 */
class RoomController extends Controller
{
    public function __construct(private GameEngine $engine)
    {
    }

    // ───────────────────────────── Creating / joining ─────────────────────────────

    public function store(Request $request)
    {
        $modes = config('snl.modes');
        $data = $request->validate([
            'type'         => 'required|in:party,online',
            'board_id'     => 'required|integer|exists:boards,id',
            'content_mode' => 'required|in:' . implode(',', array_keys($modes)),
            'intensity'    => 'required|integer|min:1|max:3',
            'adult'        => 'nullable|boolean',
            'names'        => 'required_if:type,party|array|min:2|max:' . config('snl.players.max'),
            'names.*'      => 'nullable|string|max:24',
            'colors'       => 'nullable|array',
            'avatars'      => 'nullable|array',
            'avatars.*'    => 'nullable|string|max:12',
            'avatar'       => 'nullable|string|max:12',
            'host_name'    => 'required_if:type,online|nullable|string|max:24',
            'max_players'  => 'nullable|integer|min:2|max:' . config('snl.players.max'),
        ]);

        if (($modes[$data['content_mode']]['adult'] ?? false) && !$request->boolean('adult')) {
            return back()->withInput()->withErrors(['adult' => 'Please confirm that everyone playing is 18 or over for this mode.']);
        }

        $board = Board::findOrFail($data['board_id']);
        try {
            if ($data['type'] === 'party') {
                $entries = [];
                foreach (array_values($data['names']) as $i => $name) {
                    $entries[] = ['name' => (string) $name, 'color' => $data['colors'][$i] ?? null, 'avatar' => $data['avatars'][$i] ?? null];
                }
                $room = $this->engine->createParty($board, $entries, $data['content_mode'], (int) $data['intensity']);
            } else {
                [$room, $host] = $this->engine->createOnline(
                    $board, (string) $data['host_name'], $data['content_mode'], (int) $data['intensity'], (int) ($data['max_players'] ?? 8), $data['avatar'] ?? null
                );
                $this->rememberMe($request, $room, $host);
            }
        } catch (GameException $e) {
            return back()->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('play.show', $room->token);
    }

    public function joinStore(Request $request)
    {
        $data = $request->validate([
            'code'  => 'required|string|max:8',
            'name'  => 'required|string|max:24',
            'adult' => 'nullable|boolean',
            'avatar' => 'nullable|string|max:12',
        ]);
        $room = Room::where('join_code', strtoupper(trim($data['code'])))->where('type', 'online')->first();
        if (!$room) {
            return back()->withInput()->withErrors(['code' => "We couldn't find a room with that code."]);
        }
        if ((config("snl.modes.{$room->content_mode}.adult") ?? false) && !$request->boolean('adult')) {
            return back()->withInput()->withErrors(['adult' => 'This is a 18+ room — please confirm you are 18 or over.']);
        }

        // Already seated here (refreshed the page, or tapped Join twice)? Just take them back in.
        if ($existing = $this->meFor($request, $room)) {
            return redirect()->route('play.show', $room->token);
        }
        try {
            $player = $this->engine->joinOnline($room, $data['name'], $data['avatar'] ?? null);
        } catch (GameException $e) {
            return back()->withInput()->withErrors(['code' => $e->getMessage()]);
        }
        $this->rememberMe($request, $room, $player);

        return redirect()->route('play.show', $room->token);
    }

    // ───────────────────────────── The table ─────────────────────────────

    public function show(Request $request, Room $room)
    {
        $me = $this->meFor($request, $room);

        if ($room->isOnline() && !$me) {
            $me = $this->adoptRematchSeat($request, $room);
        }
        if ($room->isOnline() && !$me) {
            return redirect()->route('join', ['code' => $room->join_code])
                ->withErrors(['code' => $room->status === 'open' ? 'Pop your name in to join this game.' : 'That game has already started.']);
        }

        $room->load('board');
        $mode = config("snl.modes.{$room->content_mode}");

        return view('play', [
            'room'      => $room,
            'board'     => $room->board,
            'positions' => $room->board->positions(),
            'state'     => $this->engine->state($room, $me?->id),
            'meId'      => $me?->id,
            'mode'      => $mode,
            'tileKinds' => $this->tileKindsOnBoard($room->board),
            'reactions' => config('snl.reactions'),
            'timing'    => config('snl.timing'),
        ]);
    }

    public function state(Request $request, Room $room): JsonResponse
    {
        return response()->json($this->engine->state($room, $this->meFor($request, $room)?->id));
    }

    public function roll(Request $request, Room $room): JsonResponse
    {
        return $this->act($request, $room, function (?Player $me) use ($room) {
            $actorId = $room->isParty() ? Room::findOrFail($room->id)->current_player_id : $this->requireMe($me)->id;
            $this->engine->roll($room, (int) $actorId);
        });
    }

    public function decide(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate(['choice' => 'required|in:use,skip,target', 'target_id' => 'nullable|integer']);

        return $this->act($request, $room, function (?Player $me) use ($room, $data) {
            $actorId = $room->isParty()
                ? (int) (Room::findOrFail($room->id)->pending['actor_id'] ?? 0)
                : $this->requireMe($me)->id;
            $this->engine->decide($room, $actorId, $data['choice'], isset($data['target_id']) ? (int) $data['target_id'] : null);
        });
    }

    public function start(Request $request, Room $room): JsonResponse
    {
        return $this->act($request, $room, fn (?Player $me) => $this->engine->start($room, $this->requireMe($me)->id));
    }

    public function leave(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate(['player_id' => 'nullable|integer']);

        return $this->act($request, $room, function (?Player $me) use ($room, $data) {
            $id = $room->isParty() ? (int) ($data['player_id'] ?? 0) : $this->requireMe($me)->id;
            $this->engine->leave($room, $id);
        });
    }

    public function react(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate(['reaction' => 'required|string|max:24']);

        return $this->act($request, $room, function (?Player $me) use ($room, $data) {
            if ($room->isOnline()) {
                $this->engine->react($room, $this->requireMe($me)->id, $data['reaction']);
            }
        });
    }

    public function rematch(Request $request, Room $room): JsonResponse
    {
        try {
            $new = $this->engine->rematch($room);
        } catch (GameException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
        // Whoever pressed it gets their seat in the new room straight away.
        if ($room->isOnline() && ($me = $this->meFor($request, $room))) {
            $seat = Player::where('room_id', $new->id)->where('prev_player_id', $me->id)->first();
            if ($seat) {
                $this->rememberMe($request, $new, $seat);
            }
        }

        return response()->json(['redirect' => route('play.show', $new->token)]);
    }

    // ───────────────────────────── Helpers ─────────────────────────────

    /** Runs a game action, then returns the fresh table state (or a friendly error). */
    private function act(Request $request, Room $room, callable $fn): JsonResponse
    {
        $me = $this->meFor($request, $room);
        try {
            $fn($me);
        } catch (GameException $e) {
            return response()->json(['error' => $e->getMessage()] + $this->engine->state($room, $me?->id), 422);
        } catch (LockTimeoutException) {
            return response()->json(['error' => 'The table is busy — try again.'], 429);
        }

        return response()->json($this->engine->state($room, $me?->id));
    }

    private function requireMe(?Player $me): Player
    {
        return $me ?? throw new GameException("You're not seated at this table.");
    }

    private function meFor(Request $request, Room $room): ?Player
    {
        $id = $request->session()->get("snl.me.{$room->id}");

        return $id ? Player::where('id', $id)->where('room_id', $room->id)->first() : null;
    }

    private function rememberMe(Request $request, Room $room, Player $player): void
    {
        $request->session()->put("snl.me.{$room->id}", $player->id);
    }

    /** Someone who played the previous game follows the rematch link and gets a seat automatically. */
    private function adoptRematchSeat(Request $request, Room $room): ?Player
    {
        if (!$room->rematch_of) {
            return null;
        }
        $oldMeId = $request->session()->get("snl.me.{$room->rematch_of}");
        if (!$oldMeId) {
            return null;
        }
        $seat = Player::where('room_id', $room->id)->where('prev_player_id', $oldMeId)->first();
        if (!$seat && $room->status === 'open') {
            $old = Player::find($oldMeId);
            if ($old) {
                try {
                    $seat = $this->engine->joinOnline($room, $old->name, $old->avatar);
                } catch (GameException) {
                    return null;
                }
            }
        }
        if ($seat) {
            $this->rememberMe($request, $room, $seat);
        }

        return $seat;
    }

    /** Which tile kinds actually appear on this board — for the in-game "how to play" key. */
    private function tileKindsOnBoard(Board $board): array
    {
        $present = $board->tiles()->pluck('kind')->unique()->all();
        $out = [];
        foreach (config('snl.tile_kinds') as $key => $k) {
            if ($key !== 'blank' && in_array($key, $present, true)) {
                $out[$key] = ['label' => $k['label'], 'icon' => $k['icon'], 'help' => $k['help']];
            }
        }

        return $out;
    }
}
