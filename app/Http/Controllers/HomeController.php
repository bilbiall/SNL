<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Support\Avatars;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // One look per avatar on the roster (random skin shade) for the bouncing token on the landing page.
        $looks = Avatars::looks();
        shuffle($looks);
        $pool = array_map(fn ($l) => $l['id'] . '-' . random_int(1, 7), array_slice($looks, 0, 24));

        return view('home', ['boards' => Board::playable(), 'pool' => $pool]);
    }

    /** Game setup. ?type=party (one device) or ?type=online (room code). */
    public function create(Request $request)
    {
        $type = $request->query('type') === 'online' ? 'online' : 'party';
        $boards = Board::playable();
        if ($boards->isEmpty()) {
            return redirect()->route('home')->withErrors(['No board is switched on yet — an admin needs to activate one in GameSet.']);
        }

        return view('setup', [
            'type'   => $type,
            'boards' => $boards,
            'modes'  => config('snl.modes'),
            'levels' => config('snl.intensities'),
            'limits' => config('snl.players'),
            'colors' => config('snl.colors'),
            'avatarDefaults' => array_map(fn ($i) => Avatars::default($i), range(0, 11)),
        ]);
    }

    public function join(Request $request)
    {
        return view('join', [
            'code' => strtoupper((string) $request->query('code', '')),
            'modes' => config('snl.modes'),
            'avatarDefault' => Avatars::default(random_int(0, 15)),
        ]);
    }
}
