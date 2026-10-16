<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Card;
use App\Models\Player;
use App\Models\Room;
use App\Models\Tile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Snakes & Liquor table engine.
 *
 * One public method per thing a player can do (roll, decide, leave, start…).
 * Each runs under a per-room lock inside a transaction, so two phones tapping at
 * once can never both act on the same turn. Everything that happens at the table
 * is appended to the room's replay log (Room::log) — clients animate from that log
 * in order, so every device sees the same story even if it polls late.
 *
 * Turn pipeline:
 *   roll → [Reroll prompt?] → move (overshoot bounces back) → resolve tile
 *        → [pick-a-friend prompt?] → [Shield prompt?] → advance turn / win
 *
 * A "pending" prompt waits on exactly one player (not always the roller — a Shield
 * prompt waits on whoever is being targeted) and is stored on the room.
 */
class GameEngine
{
    private Room $room;

    /** @var Collection<int, Player> keyed by id */
    private Collection $players;

    /** @var Collection<int, Tile> keyed by number */
    private Collection $tiles;

    /** Milliseconds the most recent movement will visibly take on screen. */
    private int $delay = 0;

    private const FALLBACK = [
        'drinks' => [
            'self' => 'Take a sip.', 'pick' => 'Pick someone to take a sip.', 'everyone' => 'Everybody take a sip.',
            'duel' => 'The loser takes a sip.', 'fact' => 'Share a fun fact with the table.', 'rule' => 'No pointing for the rest of the game.',
        ],
        'family' => [
            'self' => 'Do your best victory dance!', 'pick' => 'Pick someone to do ten star jumps.', 'everyone' => 'Everybody cheer as loud as you can!',
            'duel' => 'The loser does a silly walk.', 'fact' => 'Share a fun fact with the table.', 'rule' => 'Everyone must speak in a whisper until the next rule.',
        ],
    ];

    // ───────────────────────────── Room setup ─────────────────────────────

    /** One device, 2–12 named players, game starts immediately. */
    public function createParty(Board $board, array $entries, string $mode, int $intensity): Room
    {
        $this->validateSetup($board, $mode, $intensity);
        $cfg = config('snl.players');
        if (count($entries) < $cfg['min'] || count($entries) > $cfg['max']) {
            throw new GameException("Party games need {$cfg['min']}–{$cfg['max']} players.");
        }

        return DB::transaction(function () use ($board, $entries, $mode, $intensity) {
            $room = Room::create([
                'token' => Room::newToken(), 'board_id' => $board->id, 'type' => 'party',
                'content_mode' => $mode, 'intensity' => $intensity, 'status' => 'active',
                'max_players' => config('snl.players.max'),
            ]);
            $this->seedPlayers($room, $entries);
            $first = $room->players()->first();
            $room->update(['current_player_id' => $first->id, 'turn_started_at' => now(), 'host_player_id' => $first->id]);
            $room->log(['type' => 'start']);
            $room->save();

            return $room;
        });
    }

    /** Online: creates a lobby with the host seated. @return array{0: Room, 1: Player} */
    public function createOnline(Board $board, string $hostName, string $mode, int $intensity, int $max): array
    {
        $this->validateSetup($board, $mode, $intensity);
        $cfg = config('snl.players');

        return DB::transaction(function () use ($board, $hostName, $mode, $intensity, $max, $cfg) {
            $room = Room::create([
                'token' => Room::newToken(), 'join_code' => Room::newJoinCode(), 'board_id' => $board->id, 'type' => 'online',
                'content_mode' => $mode, 'intensity' => $intensity, 'status' => 'open',
                'max_players' => max($cfg['min'], min($cfg['max'], $max)),
            ]);
            $this->seedPlayers($room, [['name' => $hostName]]);
            $host = $room->players()->first();
            $room->update(['host_player_id' => $host->id]);

            return [$room, $host];
        });
    }

    public function joinOnline(Room $room, string $name): Player
    {
        return $this->locked($room, function () use ($name) {
            $room = $this->room;
            if ($room->status !== 'open') {
                throw new GameException($room->status === 'completed' ? 'That game has already finished.' : 'That game has already started.');
            }
            if ($this->players->where('status', 'active')->count() >= $room->max_players) {
                throw new GameException('That room is full.');
            }

            return $this->addPlayer($room, ['name' => $name]);
        });
    }

    /** Host starts an online lobby once at least two people are in. */
    public function start(Room $room, int $actorId): void
    {
        $this->locked($room, function () use ($actorId) {
            $room = $this->room;
            if ($room->status !== 'open') {
                throw new GameException('This game has already started.');
            }
            if ($room->host_player_id !== $actorId) {
                throw new GameException('Only the host can start the game.');
            }
            $active = $this->players->where('status', 'active')->sortBy('turn_order')->values();
            if ($active->count() < config('snl.players.min')) {
                throw new GameException('You need at least two players to start.');
            }
            $room->status = 'active';
            $room->current_player_id = $active->first()->id;
            $room->turn_started_at = now();
            $room->log(['type' => 'start']);
        });
    }

    /** Same table, fresh game. Party: players copy over and play starts at once.
     *  Online: only the host is seated — everyone else is pulled in as they follow the redirect. */
    public function rematch(Room $old): Room
    {
        if ($old->rematch_token) {
            return Room::where('token', $old->rematch_token)->firstOrFail();
        }

        return DB::transaction(function () use ($old) {
            $old = Room::lockForUpdate()->findOrFail($old->id);
            if ($old->rematch_token) {
                return Room::where('token', $old->rematch_token)->firstOrFail();
            }
            if (!$old->isCompleted()) {
                throw new GameException('The game is still going.');
            }

            $new = Room::create([
                'token' => Room::newToken(), 'join_code' => $old->isOnline() ? Room::newJoinCode() : null,
                'board_id' => $old->board_id, 'type' => $old->type, 'content_mode' => $old->content_mode,
                'intensity' => $old->intensity, 'max_players' => $old->max_players,
                'status' => $old->isParty() ? 'active' : 'open', 'rematch_of' => $old->id,
            ]);

            $keep = $old->players()->where('status', '!=', 'left')->get();
            if ($old->isOnline()) {
                $keep = $keep->where('id', $old->host_player_id)->values();
            }
            $this->seedPlayers($new, $keep->map(fn (Player $p) => [
                'name' => $p->name, 'color' => $p->color, 'emoji' => $p->emoji, 'prev' => $p->id,
            ])->all());

            $players = $new->players()->get();
            $new->host_player_id = $players->first()?->id;
            if ($new->isParty()) {
                $new->current_player_id = $players->first()->id;
                $new->turn_started_at = now();
                $new->log(['type' => 'start']);
            }
            $new->save();

            $old->rematch_token = $new->token;
            $old->save();

            return $new;
        });
    }

    // ───────────────────────────── Actions ─────────────────────────────

    public function roll(Room $room, int $actorId): void
    {
        $this->locked($room, function () use ($actorId) {
            $this->expire();
            $this->assertPlaying();
            $room = $this->room;
            if ($room->pending) {
                throw new GameException('Finish the current prompt first.');
            }
            if ($room->current_player_id !== $actorId) {
                throw new GameException("It's not your turn yet.");
            }
            $actor = $this->players[$actorId];
            if (!$actor->isActive()) {
                throw new GameException('You are no longer in this game.');
            }

            $value = random_int(1, 6);
            [$landing, $kind] = $this->previewLanding($actor->position, $value);

            // Only offer a Reroll when the landing would hurt — keeps the flow quick.
            if ($actor->power('reroll') > 0 && in_array($kind, ['self', 'duel', 'snake_head'], true)) {
                $this->setPending([
                    'type' => 'reroll', 'actor_id' => $actorId, 'roll' => $value, 'from' => $actor->position,
                    'preview_tile' => $landing, 'preview_kind' => $kind,
                ]);

                return;
            }

            $this->resolveRoll($actor, $value, $actor->position);
        });
    }

    /**
     * Answer the room's pending prompt.
     *   reroll: choice use|skip
     *   shield: choice use|skip
     *   pick:   choice target + $targetId
     */
    public function decide(Room $room, int $actorId, string $choice, ?int $targetId = null): void
    {
        $this->locked($room, function () use ($actorId, $choice, $targetId) {
            $this->expire();
            $this->assertPlaying();
            $room = $this->room;
            $pending = $room->pending;
            if (!$pending) {
                throw new GameException('There is nothing to decide right now.');
            }
            if ((int) $pending['actor_id'] !== $actorId) {
                throw new GameException('That prompt is for someone else.');
            }
            $this->answerPending($pending, $choice, $targetId);
        });
    }

    /** A player bows out ("I'm out"). Never penalises; the game carries on without them. */
    public function leave(Room $room, int $playerId): void
    {
        $this->locked($room, function () use ($playerId) {
            $room = $this->room;
            $p = $this->players[$playerId] ?? null;
            if (!$p || $p->status === 'left') {
                return;
            }
            $this->removePlayer($p);
        });
    }

    /** Online reactions — an emoji that floats up on everyone's screen. */
    public function react(Room $room, int $playerId, string $emoji): void
    {
        if (!in_array($emoji, config('snl.reactions'), true)) {
            return;
        }
        $this->locked($room, function () use ($playerId, $emoji) {
            if (isset($this->players[$playerId]) && $this->room->status !== 'completed') {
                $this->room->log(['type' => 'react', 'player_id' => $playerId, 'emoji' => $emoji]);
            }
        });
    }

    // ───────────────────────────── Roll resolution ─────────────────────────────

    /** Where a raw roll would land (overshoot bounce applied) and what kind of tile is there. */
    private function previewLanding(int $from, int $value): array
    {
        $n = $this->room->board->tile_count;
        $to = $from + $value;
        if ($to > $n) {
            $to = $n - ($value - ($n - $from));
        }
        if ($to >= $n) {
            return [$n, 'finish'];
        }

        return [$to, $this->tile($to)?->kind ?? 'blank'];
    }

    private function resolveRoll(Player $actor, int $value, int $from): void
    {
        $room = $this->room;
        $n = $room->board->tile_count;
        $target = $from + $value;
        $overshoot = null;

        if ($target > $n) {
            $needed = $n - $from;
            $bounced = $n - ($value - $needed);
            $hop = array_merge(range($from + 1, $n), range($n - 1, $bounced, -1));
            $overshoot = ['needed' => $needed, 'bounced_to' => $bounced];
            $actor->position = $bounced;
        } else {
            $hop = range($from + 1, $target);
            $actor->position = $target;
        }

        $cfg = config('snl.timing');
        $this->delay = $cfg['pre_hop_ms'] + count($hop) * $cfg['hop_ms'] + $cfg['landing_extra_ms'];
        $actor->bump('rolls');

        $room->log([
            'type' => 'roll', 'player_id' => $actor->id, 'roll' => $value, 'from' => $from,
            'hop_path' => $hop, 'to' => $actor->position, 'overshoot' => $overshoot,
        ]);

        if ($actor->position >= $n) {
            $this->win($actor);

            return;
        }

        $this->resolveTile($actor, $this->tile($actor->position));

        if (!$room->pending) {
            $this->endTurn($this->delay);
        }
    }

    private function resolveTile(Player $actor, ?Tile $tile, ?string $forceKind = null, bool $spin = false): void
    {
        $kind = $forceKind ?? ($tile?->kind ?? 'blank');
        $number = $tile?->number;

        switch ($kind) {
            case 'snake_head':
            case 'ladder_bottom':
                if ($tile && $tile->isMovement()) {
                    $actor->bump($kind === 'snake_head' ? 'snakes' : 'ladders');
                    $this->room->log([
                        'type' => 'move', 'player_id' => $actor->id, 'via' => $kind,
                        'from' => $actor->position, 'to' => $tile->target_number,
                    ]);
                    $actor->position = $tile->target_number;
                    $this->delay += config('snl.timing.move_extra_ms');
                }
                break;

            case 'self':
                $card = $this->cardFor('self', $tile, $spin);
                $this->hit($actor, [$actor], $card, $number);
                break;

            case 'everyone':
                $card = $this->cardFor('everyone', $tile, $spin);
                $this->commitHit($actor, $this->activePlayers()->all(), $card, $number);
                break;

            case 'pick':
            case 'duel':
            case 'swap':
                $card = $kind === 'swap'
                    ? ['kind' => 'swap', 'icon' => '🔀', 'text' => $tile?->label ?: 'Swap places with a player of your choice!', 'spin' => $spin]
                    : $this->cardFor($kind, $tile, $spin);
                $eligible = $this->activePlayers()->where('id', '!=', $actor->id)->pluck('id')->values()->all();
                if (!$eligible) {
                    break;
                }
                $this->setPending([
                    'type' => 'pick', 'actor_id' => $actor->id, 'sub' => $kind, 'card' => $card,
                    'eligible' => $eligible, 'tile' => $number,
                ]);
                break;

            case 'fact':
                $card = $this->cardFor('fact', $tile, $spin);
                $this->room->log(['type' => 'card'] + $card + ['source_id' => $actor->id, 'target_ids' => [], 'tile' => $number]);
                break;

            case 'rule':
                $card = $this->cardFor('rule', $tile, $spin);
                $this->room->active_rule = mb_substr($card['text'], 0, 220);
                $this->room->log(['type' => 'rule', 'player_id' => $actor->id, 'text' => $card['text'], 'icon' => $card['icon'], 'spin' => $spin]);
                break;

            case 'power':
                $this->grantPower($actor, $spin);
                break;

            case 'spin':
                $this->resolveTile($actor, $tile, $this->spinKind(), true);
                break;

            default: // blank / start
                if ($spin) {
                    $this->room->log(['type' => 'card', 'kind' => 'blank', 'text' => 'The wheel stops on… nothing! Lucky you.', 'icon' => '🎰', 'spin' => true,
                        'source_id' => $actor->id, 'target_ids' => [], 'tile' => $number]);
                }
                break;
        }
    }

    private function spinKind(): string
    {
        $table = config('snl.spin_table');
        $roll = random_int(1, array_sum($table));
        foreach ($table as $kind => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $kind;
            }
        }

        return 'blank';
    }

    private function grantPower(Player $actor, bool $spin): void
    {
        $keys = array_keys(config('snl.powers'));
        // Prefer a power the player isn't maxed on.
        $open = array_values(array_filter($keys, fn ($k) => $actor->power($k) < config("snl.powers.$k.max")));
        if (!$open) {
            $this->room->log(['type' => 'power', 'player_id' => $actor->id, 'power' => null, 'spin' => $spin]);

            return;
        }
        $key = $open[array_rand($open)];
        $actor->addPower($key, 1);
        $this->room->log(['type' => 'power', 'player_id' => $actor->id, 'power' => $key, 'spin' => $spin]);
    }

    // ───────────────────────────── Cards & hits ─────────────────────────────

    /** @return array{kind: string, text: string, icon: string, spin: bool} */
    private function cardFor(string $kind, ?Tile $tile, bool $spin): array
    {
        $icon = $tile?->icon
            ?: (config("snl.mode_icons.{$this->room->content_mode}.$kind") ?? config("snl.tile_kinds.$kind.icon") ?? '🃏');
        $text = $tile?->label;
        if (!$text || $spin) {
            // A spin ignores the tile's own fixed text — it's a different effect than the tile normally shows.
            $drawn = Card::draw($this->room, $kind);
            $text = $drawn?->text
                ?? (self::FALLBACK[$this->room->content_mode][$kind] ?? self::FALLBACK['family'][$kind] ?? 'Enjoy the moment!');
        }

        return ['kind' => $kind, 'text' => $text, 'icon' => $icon, 'spin' => $spin];
    }

    /** A hit on a single player — pauses for their Shield if they hold one. */
    private function hit(Player $source, array $targets, array $card, ?int $tile): void
    {
        if (count($targets) === 1 && $targets[0]->power('shield') > 0) {
            $this->setPending([
                'type' => 'shield', 'actor_id' => $targets[0]->id, 'source_id' => $source->id,
                'card' => $card, 'tile' => $tile,
            ]);

            return;
        }
        $this->commitHit($source, $targets, $card, $tile);
    }

    private function commitHit(Player $source, array $targets, array $card, ?int $tile): void
    {
        foreach ($targets as $t) {
            $t->bump('hits');
        }
        if (in_array($card['kind'], ['pick', 'duel'], true) && !(count($targets) === 1 && $targets[0]->id === $source->id)) {
            $source->bump('picks');
        }
        $this->room->log([
            'type' => 'card', 'kind' => $card['kind'], 'text' => $card['text'], 'icon' => $card['icon'], 'spin' => $card['spin'] ?? false,
            'source_id' => $source->id, 'target_ids' => array_map(fn ($t) => $t->id, $targets), 'tile' => $tile,
        ]);
    }

    // ───────────────────────────── Pending prompts ─────────────────────────────

    private function setPending(array $pending): void
    {
        $this->room->pending = $pending;
        $this->room->pending_started_at = now();
    }

    private function clearPending(): void
    {
        $this->room->pending = null;
        $this->room->pending_started_at = null;
    }

    private function answerPending(array $pending, string $choice, ?int $targetId): void
    {
        $room = $this->room;
        $turnOwner = $this->players[$room->current_player_id];

        switch ($pending['type']) {
            case 'reroll':
                if (!in_array($choice, ['use', 'skip'], true)) {
                    throw new GameException('Choose use or skip.');
                }
                $actor = $this->players[$pending['actor_id']];
                $value = (int) $pending['roll'];
                $this->clearPending();
                if ($choice === 'use' && $actor->power('reroll') > 0) {
                    $actor->addPower('reroll', -1);
                    $new = random_int(1, 6);
                    $room->log(['type' => 'reroll', 'player_id' => $actor->id, 'from_roll' => $value, 'new_roll' => $new]);
                    $value = $new;
                }
                $this->resolveRoll($actor, $value, (int) $pending['from']);

                return;

            case 'shield':
                if (!in_array($choice, ['use', 'skip'], true)) {
                    throw new GameException('Choose use or skip.');
                }
                $target = $this->players[$pending['actor_id']];
                $source = $this->players[$pending['source_id']] ?? $turnOwner;
                $this->clearPending();
                if ($choice === 'use' && $target->power('shield') > 0) {
                    $target->addPower('shield', -1);
                    $target->bump('dodges');
                    $room->log([
                        'type' => 'shield', 'player_id' => $target->id, 'source_id' => $source->id,
                        'text' => $pending['card']['text'], 'icon' => $pending['card']['icon'],
                    ]);
                } else {
                    $this->commitHit($source, [$target], $pending['card'], $pending['tile'] ?? null);
                }
                break;

            case 'pick':
                if ($choice !== 'target' || !$targetId || !in_array($targetId, $pending['eligible'], true)) {
                    throw new GameException('Pick one of the players.');
                }
                $actor = $this->players[$pending['actor_id']];
                $target = $this->players[$targetId];
                $this->clearPending();
                $this->resolvePick($pending, $actor, $target);
                break;

            default:
                $this->clearPending();
        }

        // A prompt can chain into another (pick → shield). Only finish the turn once nothing is waiting.
        if (!$room->pending && $room->status === 'active') {
            $this->endTurn(900);
        }
    }

    private function resolvePick(array $pending, Player $actor, Player $target): void
    {
        $card = $pending['card'];
        $tile = $pending['tile'] ?? null;

        switch ($pending['sub']) {
            case 'swap':
                $a = $actor->position;
                $b = $target->position;
                $actor->position = $b;
                $target->position = $a;
                $actor->bump('swaps');
                $this->room->log([
                    'type' => 'swap', 'a_id' => $actor->id, 'b_id' => $target->id, 'a_pos' => $b, 'b_pos' => $a,
                    'text' => $card['text'], 'icon' => $card['icon'], 'spin' => $card['spin'] ?? false,
                ]);
                break;

            case 'duel':
                for ($i = 0; $i < 5; $i++) {
                    $ra = random_int(1, 6);
                    $rb = random_int(1, 6);
                    if ($ra !== $rb) {
                        break;
                    }
                }
                $loser = $ra === $rb ? (random_int(0, 1) ? $actor : $target) : ($ra < $rb ? $actor : $target);
                $winner = $loser->id === $actor->id ? $target : $actor;
                $winner->bump('duels_won');
                $this->room->log([
                    'type' => 'duel', 'a_id' => $actor->id, 'b_id' => $target->id, 'a_roll' => $ra, 'b_roll' => $rb,
                    'loser_id' => $loser->id,
                ]);
                $this->hit($winner, [$loser], $card, $tile);
                break;

            default: // pick
                $this->hit($actor, [$target], $card, $tile);
        }
    }

    /** Timed-out prompts resolve themselves in the safest way (never burns a power, never skips a hit unfairly). */
    private function autoResolvePending(): void
    {
        $p = $this->room->pending;
        if (!$p) {
            return;
        }
        if ($p['type'] === 'pick') {
            $this->answerPending($p, 'target', $p['eligible'][array_rand($p['eligible'])]);
        } else {
            $this->answerPending($p, 'skip', null);
        }
    }

    // ───────────────────────────── Turn flow ─────────────────────────────

    private function endTurn(int $delayMs): void
    {
        $room = $this->room;
        $this->clearPending();

        $active = $this->activePlayers();
        if ($active->count() < 2) {
            $this->finishByAttrition($active->first());

            return;
        }

        $cur = $this->players[$room->current_player_id] ?? null;
        if ($cur) {
            $cur->missed_turns = 0;
        }
        $curOrder = $cur?->turn_order ?? -1;
        $next = $active->first(fn (Player $p) => $p->turn_order > $curOrder);
        if (!$next) {
            $next = $active->first();
            $room->round = (int) $room->round + 1;
        }
        $room->current_player_id = $next->id;
        $room->turn_started_at = now()->addMilliseconds(max(0, $delayMs));
    }

    private function win(Player $winner): void
    {
        $winner->status = 'won';
        $room = $this->room;
        $room->status = 'completed';
        $room->winner_player_id = $winner->id;
        $room->current_player_id = null;
        $this->clearPending();
        $room->log(['type' => 'win', 'player_id' => $winner->id]);
    }

    /** Everyone else bowed out — the last player standing takes it. */
    private function finishByAttrition(?Player $last): void
    {
        $room = $this->room;
        $room->status = 'completed';
        $room->current_player_id = null;
        $this->clearPending();
        if ($last) {
            $last->status = 'won';
            $room->winner_player_id = $last->id;
            $room->log(['type' => 'win', 'player_id' => $last->id, 'by_default' => true]);
        }
    }

    private function removePlayer(Player $p): void
    {
        $room = $this->room;
        $wasCurrent = $room->current_player_id === $p->id;
        $p->status = 'left';
        $room->log(['type' => 'left', 'player_id' => $p->id]);

        if ($room->status === 'open') {
            // Lobby: just free the seat; hand the host crown on if needed.
            if ($room->host_player_id === $p->id) {
                $room->host_player_id = $this->activePlayers()->first()?->id;
            }

            return;
        }
        if ($room->status !== 'active') {
            return;
        }
        if ($room->pending && (int) $room->pending['actor_id'] === $p->id) {
            $this->clearPending();
        }
        if ($this->activePlayers()->count() < 2) {
            $this->finishByAttrition($this->activePlayers()->first());

            return;
        }
        if ($room->host_player_id === $p->id) {
            $room->host_player_id = $this->activePlayers()->first()->id;
        }
        if ($wasCurrent || !$room->current_player_id) {
            $this->endTurn(0);
        }
    }

    /** Online only: idle turns pass, unanswered prompts resolve, absentees are dropped. */
    private function expire(): void
    {
        $room = $this->room;
        if (!$room->isOnline() || $room->status !== 'active') {
            return;
        }
        $cfg = config('snl.timing');

        if ($room->pending) {
            if ($room->pending_started_at && now()->timestamp - $room->pending_started_at->timestamp >= $cfg['pending_seconds']) {
                $this->autoResolvePending();
            }

            return;
        }
        if (!$room->turn_started_at || $room->turn_started_at->isFuture()) {
            return;
        }
        if (now()->timestamp - $room->turn_started_at->timestamp < $cfg['turn_seconds']) {
            return;
        }
        $p = $this->players[$room->current_player_id] ?? null;
        if (!$p) {
            return;
        }
        $p->missed_turns = (int) $p->missed_turns + 1;
        $room->log(['type' => 'skip', 'player_id' => $p->id]);
        if ($p->missed_turns >= $cfg['missed_turn_limit']) {
            $this->removePlayer($p);
        } else {
            $this->endTurn(0);
        }
    }

    /** True when polling could change something — lets state() skip the lock the rest of the time. */
    private function needsExpiry(Room $room): bool
    {
        if (!$room->isOnline() || $room->status !== 'active') {
            return false;
        }
        $cfg = config('snl.timing');
        if ($room->pending) {
            return $room->pending_started_at && now()->timestamp - $room->pending_started_at->timestamp >= $cfg['pending_seconds'];
        }

        return $room->turn_started_at && !$room->turn_started_at->isFuture()
            && now()->timestamp - $room->turn_started_at->timestamp >= $cfg['turn_seconds'];
    }

    // ───────────────────────────── State snapshot ─────────────────────────────

    /** Everything a client needs to draw the table. Runs lazy expiry first. */
    public function state(Room $room, ?int $meId): array
    {
        if ($this->needsExpiry($room)) {
            $this->locked($room, fn () => $this->expire());
        }
        $room = Room::with(['board', 'players'])->findOrFail($room->id);
        $cfg = config('snl.timing');

        $pending = $room->pending;
        $pendingView = null;
        if ($pending) {
            $pendingView = match ($pending['type']) {
                'reroll' => ['type' => 'reroll', 'actor_id' => $pending['actor_id'], 'roll' => $pending['roll'],
                    'preview_tile' => $pending['preview_tile'], 'preview_kind' => $pending['preview_kind']],
                'shield' => ['type' => 'shield', 'actor_id' => $pending['actor_id'], 'source_id' => $pending['source_id'], 'card' => $pending['card']],
                'pick'   => ['type' => 'pick', 'actor_id' => $pending['actor_id'], 'sub' => $pending['sub'], 'card' => $pending['card'], 'eligible' => $pending['eligible']],
                default  => null,
            };
        }

        $turnLeft = null;
        if ($room->isOnline() && $room->status === 'active' && !$pending && $room->turn_started_at) {
            $turnLeft = $room->turn_started_at->isFuture()
                ? $cfg['turn_seconds']
                : max(0, $cfg['turn_seconds'] - (now()->timestamp - $room->turn_started_at->timestamp));
        }
        $pendingLeft = ($room->isOnline() && $pending && $room->pending_started_at)
            ? max(0, $cfg['pending_seconds'] - (now()->timestamp - $room->pending_started_at->timestamp))
            : null;

        $players = $room->players->map(fn (Player $p) => [
            'id' => $p->id, 'name' => $p->name, 'color' => $p->color, 'emoji' => $p->emoji,
            'position' => $p->position, 'status' => $p->status, 'turn_order' => $p->turn_order,
            'powers' => ['shield' => $p->power('shield'), 'reroll' => $p->power('reroll')],
            'is_host' => $p->id === $room->host_player_id, 'missed' => $p->missed_turns,
        ])->values()->all();

        return [
            'room' => [
                'token' => $room->token, 'type' => $room->type, 'status' => $room->status,
                'content_mode' => $room->content_mode, 'intensity' => $room->intensity,
                'join_code' => $room->join_code, 'round' => $room->round, 'active_rule' => $room->active_rule,
                'current_player_id' => $room->current_player_id, 'winner_player_id' => $room->winner_player_id,
                'host_player_id' => $room->host_player_id, 'max_players' => $room->max_players,
                'tile_count' => $room->board->tile_count, 'event_seq' => $room->event_seq,
                'rematch_token' => $room->rematch_token,
                'turn_seconds_left' => $turnLeft, 'pending_seconds_left' => $pendingLeft,
            ],
            'players' => $players,
            'pending' => $pendingView,
            'actions' => $room->actions ?? [],
            'me' => $meId,
            'recap' => $room->isCompleted() ? $this->recap($room) : null,
        ];
    }

    /** Fun end-of-night awards from the per-player counters. */
    private function recap(Room $room): array
    {
        $awards = [
            ['key' => 'hits',    'icon' => '🥴', 'title' => 'Took the most hits'],
            ['key' => 'dodges',  'icon' => '🛡️', 'title' => 'Master dodger'],
            ['key' => 'picks',   'icon' => '😈', 'title' => 'Chief mischief-maker'],
            ['key' => 'snakes',  'icon' => '🐍', 'title' => 'Snake magnet'],
            ['key' => 'ladders', 'icon' => '🪜', 'title' => 'Lucky climber'],
            ['key' => 'duels_won', 'icon' => '⚔️', 'title' => 'Duel champion'],
        ];
        $out = [];
        foreach ($awards as $a) {
            $best = $room->players->sortByDesc(fn (Player $p) => $p->stat($a['key']))->first();
            if ($best && $best->stat($a['key']) > 0) {
                $out[] = ['icon' => $a['icon'], 'title' => $a['title'], 'player_id' => $best->id, 'name' => $best->name, 'value' => $best->stat($a['key'])];
            }
        }
        $ranking = $room->players->sortByDesc(fn (Player $p) => $p->status === 'won' ? PHP_INT_MAX : $p->position)
            ->map(fn (Player $p) => ['id' => $p->id, 'name' => $p->name, 'color' => $p->color, 'emoji' => $p->emoji,
                'position' => $p->position, 'status' => $p->status])
            ->values()->all();

        return ['awards' => $out, 'ranking' => $ranking, 'rounds' => $room->round];
    }

    // ───────────────────────────── Internals ─────────────────────────────

    private function validateSetup(Board $board, string $mode, int $intensity): void
    {
        $cfg = config("snl.modes.$mode");
        if (!$cfg) {
            throw new GameException('Unknown game mode.');
        }
        if (!$cfg['enabled']) {
            throw new GameException("{$cfg['label']} mode isn't available yet.");
        }
        if (!isset(config('snl.intensities')[$intensity])) {
            throw new GameException('Unknown intensity.');
        }
        if (!$board->is_active) {
            throw new GameException('That board is not available.');
        }
    }

    /** @param array<int, array{name: string, color?: string, emoji?: string, prev?: int}> $entries */
    private function seedPlayers(Room $room, array $entries): void
    {
        foreach (array_values($entries) as $i => $e) {
            $this->addPlayer($room, $e, $i);
        }
    }

    private function addPlayer(Room $room, array $e, ?int $order = null): Player
    {
        $count = Player::where('room_id', $room->id)->count();
        $order ??= $count;
        $name = trim(preg_replace('/\s+/', ' ', (string) ($e['name'] ?? ''))) ?: 'Player ' . ($order + 1);
        $name = mb_substr($name, 0, 24);
        // Same name twice at one table gets a number so the picker is unambiguous.
        $base = $name;
        $n = 1;
        while (Player::where('room_id', $room->id)->where('name', $name)->exists()) {
            $name = mb_substr($base, 0, 21) . ' ' . (++$n);
        }
        $colors = config('snl.colors');
        $avatars = config('snl.avatars');

        $powers = [];
        foreach (config('snl.powers') as $key => $p) {
            $powers[$key] = $p['start'];
        }

        return Player::create([
            'room_id' => $room->id, 'name' => $name, 'turn_order' => $order,
            'color' => $e['color'] ?? $colors[$order % count($colors)],
            'emoji' => $e['emoji'] ?? $avatars[$order % count($avatars)],
            'position' => 0, 'status' => 'active', 'powers' => $powers, 'stats' => [],
            'prev_player_id' => $e['prev'] ?? null,
        ]);
    }

    private function tile(int $number): ?Tile
    {
        if (!isset($this->tiles)) {
            $this->tiles = Tile::where('board_id', $this->room->board_id)->get()->keyBy('number');
        }

        return $this->tiles->get($number);
    }

    /** @return Collection<int, Player> active players in seat order */
    private function activePlayers(): Collection
    {
        return $this->players->where('status', 'active')->sortBy('turn_order')->values();
    }

    private function assertPlaying(): void
    {
        if ($this->room->status === 'completed') {
            throw new GameException('This game is over.');
        }
        if ($this->room->status !== 'active') {
            throw new GameException('The game has not started yet.');
        }
    }

    /**
     * Runs $fn with the room locked + freshly loaded, then persists the room and every
     * player it touched, atomically. Any exception rolls the whole action back.
     */
    private function locked(Room $room, callable $fn): mixed
    {
        return Cache::lock("snl_room_{$room->id}", 10)->block(6, function () use ($room, $fn) {
            return DB::transaction(function () use ($room, $fn) {
                $this->room = Room::with('board')->lockForUpdate()->findOrFail($room->id);
                $this->players = Player::where('room_id', $room->id)->orderBy('turn_order')->get()->keyBy('id');
                unset($this->tiles);
                $this->delay = 0;

                $result = $fn();

                $this->room->save();
                $this->players->each->save();

                return $result;
            });
        });
    }
}
