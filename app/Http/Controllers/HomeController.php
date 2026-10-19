<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Room;
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

        $apk = null;
        if (is_file(public_path('downloads/snakes-and-liquor.apk')) && is_file(public_path('downloads/version.json'))) {
            $apk = json_decode(file_get_contents(public_path('downloads/version.json')), true);
        }

        return view('home', ['boards' => Board::playable(), 'pool' => $pool, 'apk' => $apk, 'openRooms' => \App\Models\Room::listed()->where('status', 'open')->count()]);
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
        $code = strtoupper((string) $request->query('code', ''));

        // An invite link (/join?code=ABCDE): look the room up so the page — and the preview card WhatsApp builds from it — can say who invited you.
        $invite = null;
        if (preg_match('/^[A-Z0-9]{4,8}$/', $code) && ($room = Room::where('join_code', $code)->whereIn('status', ['open', 'active'])->first()) && $room->isOnline() && !($room->status === 'active' && $room->team_mode)) {
            $invite = [
                'host'    => (string) ($room->players()->where('id', $room->host_player_id)->value('name') ?: 'A friend'),
                'mode'    => (string) config("snl.modes.{$room->content_mode}.label", ''),
                'players' => $room->players()->where('status', 'active')->count(),
                'max'     => (int) $room->max_players,
                'live'    => $room->status === 'active', // the game has started, but you can still jump in
            ];
        }

        return view('join', [
            'invite' => $invite,
            'code' => $code,
            'modes' => config('snl.modes'),
            'avatarDefault' => Avatars::default(random_int(0, 15)),
        ]);
    }
}
