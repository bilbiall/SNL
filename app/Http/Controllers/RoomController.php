<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Pack;
use App\Models\Player;
use App\Models\Room;
use App\Services\GameEngine;
use App\Services\GameException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            'start_shields' => 'nullable|integer|min:0|max:' . config('snl.powers.shield.max', 5),
            'names'        => 'required_if:type,party|array|min:2|max:' . config('snl.players.max'),
            'names.*'      => 'nullable|string|max:24',
            'colors'       => 'nullable|array',
            'avatars'      => 'nullable|array',
            'avatars.*'    => 'nullable|string|max:12',
            'avatar'       => 'nullable|string|max:12',
            'host_name'    => 'required_if:type,online|nullable|string|max:24',
            'public'       => 'nullable|boolean',
            'teams'        => 'nullable|boolean',
            'team_names'   => 'nullable|array|max:6',
            'team_names.*' => 'nullable|string|max:20',
            'topics'       => 'nullable|array|max:20',
            'topics.*'     => 'in:random,' . implode(',', array_keys(config('snl.topics'))),
            'team_of'      => 'nullable|array|max:12',
            'team_of.*'    => 'nullable|integer|min:1|max:6',
            'room_name'    => 'required_if:public,1|nullable|string|min:3|max:40',
            'max_players'  => 'nullable|integer|min:2|max:' . config('snl.players.max'),
        ]);

        if (($modes[$data['content_mode']]['adult'] ?? false) && !$request->boolean('adult')) {
            return back()->withInput()->withErrors(['adult' => 'Please confirm that everyone playing is 18 or over for this mode.']);
        }

        $payload = $data + ['teams' => $request->boolean('teams'), 'public' => $request->boolean('public')];

        // A paid mode: nothing is created yet. The finished setup is saved as an order and the host pays first (M-Pesa STK push);
        // the room is created from that saved setup only after the payment succeeds (see CheckoutController).
        if (($price = \App\Support\Payments::priceFor($data['content_mode'])) !== null) {
            try {
                $this->precheck($payload); // never take money for a game that could not be created
            } catch (GameException $e) {
                return back()->withInput()->withErrors([$e->getMessage()]);
            }
            $order = \App\Models\Payment::create([
                'token' => \Illuminate\Support\Str::random(40), 'status' => 'created', 'mode' => $data['content_mode'], 'amount' => $price,
                'payload' => $payload, 'env' => \App\Support\Payments::env(), 'ip' => $request->ip(),
            ]);

            return redirect()->route('checkout.show', $order->token);
        }

        try {
            $room = $this->createGame($request, $payload);
        } catch (GameException $e) {
            return back()->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('play.show', $room->token);
    }

    /** Creates the room (and, online, seats the host in this browser's session) from a validated setup. Used by free modes and, after payment, by the checkout. */
    public function createGame(Request $request, array $data): Room
    {
        $board = Board::findOrFail($data['board_id']);
        $teams = !empty($data['teams']);
        if (($data['type'] ?? '') === 'party') {
            $entries = [];
            foreach (array_values($data['names']) as $i => $name) {
                $entries[] = ['name' => (string) $name, 'color' => $data['colors'][$i] ?? null, 'avatar' => $data['avatars'][$i] ?? null,
                    'team' => $teams ? ($data['team_of'][$i] ?? null) : null]; // which team the table put this player on
            }

            return $this->engine->createParty($board, $entries, $data['content_mode'], (int) $data['intensity'], isset($data['start_shields']) ? (int) $data['start_shields'] : null, $teams, array_values($data['team_names'] ?? []), array_values($data['topics'] ?? []));
        }
        [$room, $host] = $this->engine->createOnline(
            $board, (string) $data['host_name'], $data['content_mode'], (int) $data['intensity'], (int) ($data['max_players'] ?? 8), $data['avatar'] ?? null,
            isset($data['start_shields']) ? (int) $data['start_shields'] : null,
            $this->cleanRoomName($data['room_name'] ?? null), !empty($data['public']) && filled($data['room_name'] ?? null), $teams, array_values($data['topics'] ?? [])
        );
        $this->rememberMe($request, $room, $host);

        return $room;
    }

    /** The checks that would make room creation fail, run BEFORE the customer is asked to pay. */
    private function precheck(array $d): void
    {
        $this->engine->checkSetup(Board::findOrFail($d['board_id']), $d['content_mode'], (int) $d['intensity']);
        $this->cleanRoomName($d['room_name'] ?? null);
        if (($d['type'] ?? '') === 'party') {
            $n = count(array_values($d['names'] ?? []));
            $lim = config('snl.players');
            if ($n < $lim['min'] || $n > $lim['max']) {
                throw new GameException("Party games need {$lim['min']}–{$lim['max']} players.");
            }
            if (!empty($d['teams'])) {
                $per = array_count_values(array_map('intval', array_values($d['team_of'] ?? [])));
                if ($n < 4 || $n % 2 !== 0 || count($per) < 2 || count(array_filter($per, fn ($c) => $c !== 2)) > 0 || count($per) * 2 !== $n) {
                    throw new GameException('Every team needs exactly two players, and there must be at least two teams.');
                }
            }
        }
    }

    /** Room names are shown to strangers: trim, collapse spaces, and keep them free of links and a short blocklist. */
    private function cleanRoomName(?string $name): ?string
    {
        $name = trim(preg_replace('/\s+/', ' ', strip_tags((string) $name)));
        if ($name === '') {
            return null;
        }
        if (preg_match('~https?://|www\.|\.(com|net|org|io|co)\b~i', $name) || preg_match('/\b(' . implode('|', config('snl.blocked_words', [])) . ')\b/i', $name)) {
            throw ValidationException::withMessages(['room_name' => 'Pick a friendlier room name — no links or rude words.']);
        }

        return $name;
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
                ->withErrors(['code' => in_array($room->status, ['open', 'active'], true) && !($room->status === 'active' && $room->team_mode) ? 'Pop your name in to join this game.' : ($room->status === 'completed' ? 'That game has already finished.' : 'That team game has already started.')]);
        }

        $room->load('board');
        $mode = config("snl.modes.{$room->content_mode}");

        return view('play', [
            'room'      => $room,
            'board'     => $room->board,
            'positions' => $room->board->positions(),
            'state'     => $this->engine->state($room, $me?->id),
            'meId'      => $me?->id,
            'voice'     => \App\Support\Voice::clientConfig(), // 'off' unless an admin set voice up in GameSet → Settings
            'mode'      => $mode,
            'tileKinds' => $this->tileKindsOnBoard($room->board),
            'reactions' => config('snl.reactions'),
            'music' => file_exists(public_path('audio/playback.mp3')) ? asset('audio/playback.mp3') : null,
            'timing'    => config('snl.timing'),
        ]);
    }

    /** TV mode: the same table, but nobody is "me" — it only watches. */
    public function tv(Request $request, string $code)
    {
        $room = Room::where('join_code', strtoupper(trim($code)))->first();
        abort_unless($room && $room->isOnline(), 404);
        $room->load('board');

        return view('play', [
            'room'      => $room,
            'board'     => $room->board,
            'positions' => $room->board->positions(),
            'state'     => $this->engine->state($room, null),
            'meId'      => null,
            'tv'        => true,
            'event'     => $request->boolean('event'),
            'sponsors'  => Pack::live()->whereNotNull('sponsor')->get()->filter(fn ($p) => $p->appliesTo($room->content_mode))
                ->map(fn ($p) => ['name' => $p->sponsor, 'tagline' => $p->tagline, 'logo' => $p->logo ? asset(ltrim($p->logo, '/')) : null])->values()->all(),
            'mode'      => config("snl.modes.{$room->content_mode}"),
            'tileKinds' => $this->tileKindsOnBoard($room->board),
            'reactions' => config('snl.reactions'),
            'music'     => file_exists(public_path('audio/playback.mp3')) ? asset('audio/playback.mp3') : null,
            'timing'    => config('snl.timing'),
        ]);
    }

    public function state(Request $request, Room $room): JsonResponse
    {
        $meId = $this->meFor($request, $room)?->id;

        // Long poll (the TV screen): with ?since=<last event number>&wait=<seconds>, hold the request until something happens
        // (or up to 8 s) so a roll on a phone reaches the big screen straight away instead of at the next poll tick.
        $since = $request->query('since');
        if (is_numeric($since)) {
            $request->session()->save(); // don't keep the session busy while we wait
            $until = microtime(true) + min(8.0, max(0.0, (float) $request->query('wait', 0)));
            while (microtime(true) < $until && !connection_aborted()) {
                $now = Room::whereKey($room->id)->value('event_seq');
                if ($now === null || (int) $now !== (int) $since) {
                    break;
                }
                usleep(200000);
            }
        }

        return response()->json($this->engine->state($room, $meId));
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
        // Every answer a prompt can take: reroll/shield (use|skip), pick (target), true-or-false (true|false), bluff
        // (done|call|none), verdict (proved|busted), spotlight (nailed|failed). The engine checks which fits the prompt.
        $data = $request->validate([
            'choice'    => 'required|in:use,skip,target,true,false,done,call,none,proved,busted,nailed,failed',
            'target_id' => 'nullable|integer',
        ]);

        return $this->act($request, $room, function (?Player $me) use ($room, $data) {
            $actorId = $room->isParty()
                ? (int) (Room::findOrFail($room->id)->pending['actor_id'] ?? 0)
                : $this->requireMe($me)->id;
            $this->engine->decide($room, $actorId, $data['choice'], isset($data['target_id']) ? (int) $data['target_id'] : null);
        });
    }

    public function start(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate([
            'team_names' => 'nullable|array|max:6', 'team_names.*' => 'nullable|string|max:20',
            'team_assign' => 'nullable|array|max:12', 'team_assign.*' => 'integer|min:1|max:6', // player id => team, chosen by the host in the lobby
        ]);

        return $this->act($request, $room, fn (?Player $me) => $this->engine->start($room, $this->requireMe($me)->id, array_values($data['team_names'] ?? []), $data['team_assign'] ?? null));
    }

    public function leave(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate(['player_id' => 'nullable|integer']);

        return $this->act($request, $room, function (?Player $me) use ($room, $data) {
            $id = $room->isParty() ? (int) ($data['player_id'] ?? 0) : $this->requireMe($me)->id;
            $this->engine->leave($room, $id);
        });
    }

    /** Party games: seat another player while the game is running (hamburger menu → Add a player). */
    public function addPlayer(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:24', 'avatar' => 'nullable|string|max:12']);

        return $this->act($request, $room, fn (?Player $me) => $this->engine->addPlayerMidGame($room, $data['name'], $data['avatar'] ?? null));
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

    /** "I did it" for a secret mission only the table can judge. */
    public function claimMission(Request $request, Room $room): JsonResponse
    {
        return $this->act($request, $room, function (?Player $me) use ($room) {
            $this->engine->claimMission($room, $this->requireMe($me)->id);
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
