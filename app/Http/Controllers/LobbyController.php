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

                return [
                    'name'      => $r->name,
                    'code'      => $open ? $r->join_code : null,       // only waiting rooms can be joined
                    'open'      => $open,
                    'mode'      => ['label' => $modes[$r->content_mode]['label'] ?? $r->content_mode, 'icon' => $modes[$r->content_mode]['icon'] ?? 'cards', 'adult' => (bool) ($modes[$r->content_mode]['adult'] ?? false)],
                    'intensity' => ['label' => $levels[$r->intensity]['label'] ?? '', 'icon' => $levels[$r->intensity]['icon'] ?? 'star'],
                    'players'   => $active->count(),
                    'max'       => (int) $r->max_players,
                    'full'      => $open && $active->count() >= $r->max_players,
                    'host'      => $host?->name,
                    'avatars'   => $active->take(6)->map(fn ($p) => $p->avatar)->values()->all(),
                    'round'     => (int) $r->round,
                    'minutes'   => max(0, (int) $r->created_at->diffInMinutes(now())),
                ];
            })->values()->all();
    }
}
