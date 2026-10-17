<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Tile;
use App\Support\BoardLayout;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * GameSet → Boards. Upload the art, say how many squares it has, then drag every
 * numbered square onto its real spot (positions are stored as % of the image, so
 * any upload works), and set what each square does.
 */
class GamesetBoardController extends Controller
{
    public function index(Request $request)
    {
        $boards = Board::orderBy('sort_order')->orderBy('id')->get();
        $board = ($id = (int) $request->query('board'))
            ? Board::findOrFail($id)
            : ($boards->first());

        $tiles = $board ? $board->tiles()->get() : collect();
        $groups = [];
        if ($board) {
            foreach (range(1, $board->tile_count, 10) as $i => $from) {
                $to = min($from + 9, $board->tile_count);
                $groups[] = [
                    'label' => "Squares {$from}–{$to}",
                    'tiles' => $tiles->whereBetween('number', [$from, $to])->values(),
                ];
            }
        }

        return view('gameset.boards', [
            'boards'    => $boards,
            'board'     => $board,
            'tiles'     => $tiles,
            'groups'    => $groups,
            'kinds'     => config('snl.tile_kinds'),
            'deckCounts' => \App\Models\Card::where('is_active', true)->selectRaw('kind, count(*) as n')->groupBy('kind')->pluck('n', 'kind'),
            'roomCount' => $board ? $board->rooms()->count() : 0,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:60',
            'tile_count' => 'required|integer|min:10|max:200',
            'image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);
        [$url, $w, $h] = $this->storeArt($request->file('image'));
        $n = (int) $data['tile_count'];

        $board = DB::transaction(function () use ($data, $url, $w, $h, $n) {
            $board = Board::create([
                'slug' => 'board-' . Str::lower(Str::random(8)), 'name' => $data['name'],
                'board_image' => $url, 'board_width' => $w, 'board_height' => $h, 'tile_count' => $n,
                'is_active' => false, // hidden from players until the admin has laid it out
                'sort_order' => (int) Board::max('sort_order') + 1,
            ]);
            $now = now();
            $kinds = BoardLayout::starterKinds($n);
            $rows = [];
            foreach (BoardLayout::serpentine($n) as $number => [$left, $top]) {
                $rows[] = [
                    'board_id' => $board->id, 'number' => $number,
                    'kind' => $kinds[$number]['kind'], 'target_number' => $kinds[$number]['target'],
                    'icon' => $number === $n ? 'flag' : null,
                    'label' => $number === $n ? 'Finish — reach this square to win' : null,
                    'pos_left' => $left, 'pos_top' => $top, 'created_at' => $now, 'updated_at' => $now,
                ];
            }
            DB::table('tiles')->insert($rows);

            return $board;
        });

        return redirect()->route('gameset.boards.index', ['board' => $board->id])
            ->with('success', "Board \"{$board->name}\" created and hidden from players. Drag each square onto the art, set what each does, then tick Active.");
    }

    public function settings(Request $request, Board $board)
    {
        $data = $request->validate(['name' => 'required|string|max:60']);
        $data['is_active'] = $request->boolean('is_active');

        // Never hide the last live board — players would have nothing to play on.
        if (!$data['is_active'] && !Board::where('is_active', true)->where('id', '!=', $board->id)->exists()) {
            return back()->withErrors(['At least one board must stay active — activate another one first.']);
        }
        $board->update($data);

        return back()->with('success', 'Board settings saved.');
    }

    /** Swap the art. Square positions are kept (they are % of the image), so a same-layout
     *  re-skin needs no re-mapping; a different layout does. */
    public function art(Request $request, Board $board)
    {
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192']);
        [$url, $w, $h] = $this->storeArt($request->file('image'));
        $old = $board->board_image;
        $board->update(['board_image' => $url, 'board_width' => $w, 'board_height' => $h]);
        $this->deleteArt($old);

        return back()->with('success', 'Board art replaced. Check both layouts below — drag any square that no longer sits on its tile.');
    }

    /** Growing appends blank squares in the middle for the admin to drag into place; shrinking
     *  drops the tail. Refused while a game on this board is running. */
    public function resize(Request $request, Board $board)
    {
        $n = (int) $request->validate(['tile_count' => 'required|integer|min:10|max:200'])['tile_count'];
        $old = $board->tile_count;
        if ($n === $old) {
            return back()->with('success', 'Tile count unchanged.');
        }
        if ($board->rooms()->whereIn('status', ['open', 'active'])->exists()) {
            return back()->withErrors(['Games are in progress on this board — wait until they finish (or switch the board off) before changing its size.']);
        }

        DB::transaction(function () use ($board, $n, $old) {
            if ($n > $old) {
                $now = now();
                $rows = [];
                for ($i = $old + 1; $i <= $n; $i++) {
                    $rows[] = ['board_id' => $board->id, 'number' => $i, 'kind' => 'blank', 'pos_left' => 50, 'pos_top' => 50,
                        'created_at' => $now, 'updated_at' => $now];
                }
                DB::table('tiles')->insert($rows);
            } else {
                Tile::where('board_id', $board->id)->where('number', '>', $n)->delete();
                Tile::where('board_id', $board->id)->where('target_number', '>', $n)
                    ->update(['kind' => 'blank', 'target_number' => null]);
            }
            $board->update(['tile_count' => $n]);
        });

        return back()->with('success', "Board now has {$n} squares." . ($n > $old ? ' New squares were dropped in the middle — drag them onto the art.' : ' Any snake or ladder pointing past the new end was cleared.'));
    }

    public function destroy(Board $board)
    {
        if ($board->rooms()->exists()) {
            return back()->withErrors(['This board has game history, so deleting it would lose that. Untick Active to retire it instead.']);
        }
        if ($board->is_active && !Board::where('is_active', true)->where('id', '!=', $board->id)->exists()) {
            return back()->withErrors(['This is the only live board — activate another before deleting it.']);
        }
        $image = $board->board_image;
        $board->delete(); // tiles and board-specific cards cascade
        $this->deleteArt($image);

        return redirect()->route('gameset.boards.index')->with('success', 'Board deleted.');
    }

    /** Drag-to-place calibration. `layout` picks which column pair this save writes:
     *  the normal desktop pair, or the separate mobile-landscape pair — recalibrating one never
     *  touches the other, and mobile falls back to desktop until explicitly calibrated. */
    public function positions(Request $request, Board $board)
    {
        $data = $request->validate([
            'layout'               => 'nullable|in:desktop,mobile',
            'positions'            => 'required|array|size:' . $board->tile_count,
            'positions.*.number'   => 'required|integer|min:1|max:' . $board->tile_count,
            'positions.*.pos_left' => 'required|numeric|min:0|max:100',
            'positions.*.pos_top'  => 'required|numeric|min:0|max:100',
        ]);
        $mobile = ($data['layout'] ?? 'desktop') === 'mobile';
        [$leftCol, $topCol] = $mobile ? ['pos_left_mobile', 'pos_top_mobile'] : ['pos_left', 'pos_top'];

        $byNumber = $board->tiles()->get()->keyBy('number');
        foreach ($data['positions'] as $row) {
            $byNumber->get((int) $row['number'])?->update([$leftCol => $row['pos_left'], $topCol => $row['pos_top']]);
        }

        return back()->with('success', ($mobile ? 'Mobile landscape layout' : 'Board layout') . ' saved — ' . count($data['positions']) . ' square positions updated.');
    }

    /** The rectangle where players wait before their first roll (percent of the art). Empty = back to square 1. */
    public function zone(Request $request, Board $board)
    {
        if ($request->boolean('clear')) {
            $board->update(['start_zone' => null]);

            return back()->with('success', 'Start zone removed — players will wait on square 1.');
        }
        $d = $request->validate([
            'zone_left' => 'required|numeric|min:0|max:100', 'zone_top' => 'required|numeric|min:0|max:100',
            'zone_width' => 'required|numeric|min:3|max:100', 'zone_height' => 'required|numeric|min:3|max:100',
        ]);
        $board->update(['start_zone' => [
            'left' => round($d['zone_left'], 2), 'top' => round($d['zone_top'], 2),
            'width' => round(min($d['zone_width'], 100 - $d['zone_left']), 2), 'height' => round(min($d['zone_height'], 100 - $d['zone_top']), 2),
        ]]);

        return back()->with('success', 'Start zone saved — players now wait there before their first roll.');
    }

    public function tiles(Request $request, Board $board)
    {
        $kinds = array_keys(config('snl.tile_kinds'));
        $data = $request->validate([
            'tiles'                 => 'required|array|size:' . $board->tile_count,
            'tiles.*.number'        => 'required|integer|min:1|max:' . $board->tile_count,
            'tiles.*.kind'          => 'required|in:' . implode(',', $kinds),
            'tiles.*.target_number' => 'nullable|integer|min:1|max:' . $board->tile_count,
            'tiles.*.icon'          => 'nullable|string|max:10',
            'tiles.*.label'         => 'nullable|string|max:120',
        ]);

        $byNumber = $board->tiles()->get()->keyBy('number');
        $errors = [];
        foreach ($data['tiles'] as $row) {
            $number = (int) $row['number'];
            $tile = $byNumber->get($number);
            if (!$tile) {
                continue;
            }
            $kind = $row['kind'];
            $target = !empty($row['target_number']) ? (int) $row['target_number'] : null;

            if (in_array($kind, ['snake_head', 'ladder_bottom'], true)) {
                if (!$target) {
                    $errors[] = "Square {$number} is a " . config("snl.tile_kinds.$kind.label") . ' but has no target square.';
                    continue;
                }
                if ($kind === 'snake_head' && $target >= $number) {
                    $errors[] = "Square {$number}: a snake must slide DOWN — its target must be lower than {$number}.";
                    continue;
                }
                if ($kind === 'ladder_bottom' && $target <= $number) {
                    $errors[] = "Square {$number}: a ladder must climb UP — its target must be higher than {$number}.";
                    continue;
                }
            } else {
                $target = null;
            }

            $tile->update([
                'kind' => $kind, 'target_number' => $target,
                'icon' => $row['icon'] ?? null, 'label' => $row['label'] ?? null,
            ]);
        }
        if ($errors) {
            return back()->withErrors($errors);
        }

        return back()->with('success', 'Squares saved — ' . count($data['tiles']) . ' updated.');
    }

    // ── Art storage ──

    /** Store uploaded art under public/uploads/boards (opaque → WebP/JPEG, capped at 2000px wide).
     *  @return array{0: string, 1: int, 2: int} [url, width, height] */
    private function storeArt(UploadedFile $file): array
    {
        $tmp = $file->getRealPath();
        $src = match ($file->getMimeType()) {
            'image/png'  => imagecreatefrompng($tmp),
            'image/webp' => imagecreatefromwebp($tmp),
            default      => imagecreatefromjpeg($tmp),
        };
        [$w, $h] = getimagesize($tmp);
        $scale = min(2000 / $w, 1);
        $dw = max(1, (int) round($w * $scale));
        $dh = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($dw, $dh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 11, 10, 22)); // flatten transparency onto the page background
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dw, $dh, $w, $h);

        $dir = public_path('uploads/boards');
        @mkdir($dir, 0755, true);
        $webp = function_exists('imagewebp');
        $name = uniqid('board_') . ($webp ? '.webp' : '.jpg');
        $webp ? imagewebp($dst, "$dir/$name", 85) : imagejpeg($dst, "$dir/$name", 85);
        imagedestroy($src);
        imagedestroy($dst);

        return ['/uploads/boards/' . $name, $dw, $dh];
    }

    private function deleteArt(?string $url): void
    {
        if ($url && str_starts_with($url, '/uploads/boards/')) {
            @unlink(public_path(ltrim($url, '/')));
        }
    }
}
