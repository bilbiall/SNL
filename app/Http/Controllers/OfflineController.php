<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Support\OfflineAssets;
use App\Support\OfflinePack;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Offline support: the game pack + the list of files to keep on the device, and the shell page that runs a
 * party game entirely in the browser (the game itself lives in public/js/snl-engine.js + snl-local.js).
 */
class OfflineController extends Controller
{
    public function pack(): JsonResponse
    {
        $pack = Cache::remember('snl_offline_pack', 60, fn () => OfflinePack::build());

        return response()->json($pack, 200, ['Cache-Control' => 'no-cache'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function assets(): JsonResponse
    {
        return response()->json(OfflineAssets::build(), 200, ['Cache-Control' => 'no-cache'], JSON_UNESCAPED_SLASHES);
    }

    /** The party table shell. Everything game-specific (board, players, state) is filled in by snl-local.js from the saved game. */
    public function local()
    {
        $board = Board::playable()->first() ?? abort(404, 'No board is switched on yet.');

        return view('play', [
            'local' => true, 'room' => null, 'board' => $board, 'positions' => [], 'state' => null, 'meId' => null,
            'mode' => null, 'tileKinds' => [], 'reactions' => config('snl.reactions'),
            'music' => file_exists(public_path('audio/playback.mp3')) ? asset('audio/playback.mp3') : null,
            'timing' => config('snl.timing'),
        ]);
    }
}
