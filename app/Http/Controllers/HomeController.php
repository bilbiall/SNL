<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', ['boards' => Board::playable()]);
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
            'avatars' => config('snl.avatars'),
        ]);
    }

    public function join(Request $request)
    {
        return view('join', ['code' => strtoupper((string) $request->query('code', '')), 'modes' => config('snl.modes')]);
    }
}
