<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\JsonResponse;

/**
 * The public game browser: online rooms a host chose to list. People can see which are waiting for players
 * (and join with one tap) and which are already under way. Private rooms never appear here — they stay code-only.
 */
class LobbyController extends Controller
{
    public function index()
    {
        return view('rooms', ['rooms' => $this->rooms()]);
    }

    /** The TV start screen: type a room code, or pick one of the public games. */
    public function tvIndex()
    {
        return view('tv', ['rooms' => $this->rooms()]);
    }

    public function tvGo(\Illuminate\Http\Request $request)
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->query('code')));
        $room = $code !== '' ? Room::where('join_code', $code)->first() : null;
        if (!$room || !$room->isOnline()) {
            return redirect()->route('tv.index')->withErrors(['code' => 'No online game with that code — check it and try again.']);
        }

        return redirect()->route('tv.show', $room->join_code);
    }

    public function list(): JsonResponse
    {
        return response()->json(['rooms' => $this->rooms(), 'at' => now()->toIso8601String()], 200, ['Cache-Control' => 'no-store']);
    }

    /** @return array<int, array<string, mixed>> */
    private function rooms(): array
    {
        $modes = config('snl.modes');
        $levels = config('snl.intensities');

        return Room::listed()->with(['players' => fn ($q) => $q->orderBy('turn_order')])
            ->orderByRaw("status = 'open' desc")->orderByDesc('created_at')->limit(60)->get()
            ->map(function (Room $r) use ($modes, $levels) {
                $active = $r->players->where('status', 'active');
                $host = $r->players->firstWhere('id', $r->host_player_id);
                $open = $r->status === 'open';
                $joinable = $open || ($r->status === 'active' && !$r->team_mode); // friends can join a running game too (not a team game)

                return [
                    'name'      => $r->name,
                    'code'      => $joinable ? $r->join_code : null,   // waiting rooms and running (non-team) games can be joined
                    'tv'        => $r->join_code,                       // anyone can watch a listed room on a TV
                    'open'      => $open,
                    'joinable'  => $joinable,
                    'mode'      => ['label' => $modes[$r->content_mode]['label'] ?? $r->content_mode, 'icon' => $modes[$r->content_mode]['icon'] ?? 'cards', 'adult' => (bool) ($modes[$r->content_mode]['adult'] ?? false)],
                    'intensity' => ['label' => $levels[$r->intensity]['label'] ?? '', 'icon' => $levels[$r->intensity]['icon'] ?? 'star'],
                    'players'   => $active->count(),
                    'max'       => (int) $r->max_players,
                    'full'      => $joinable && $active->count() >= $r->max_players,
                    'host'      => $host?->name,
                    'avatars'   => $active->take(6)->map(fn ($p) => $p->avatar)->values()->all(),
                    'round'     => (int) $r->round,
                    'minutes'   => max(0, (int) $r->created_at->diffInMinutes(now())),
                ];
            })->values()->all();
    }
}
