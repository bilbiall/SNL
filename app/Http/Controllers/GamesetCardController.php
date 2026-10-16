<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use Illuminate\Http\Request;

/** GameSet → Cards: the decks. Kind × content mode × intensity, with a bulk "one card per line" adder. */
class GamesetCardController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'drinks');
        if (!isset(config('snl.modes')[$mode])) {
            $mode = 'drinks';
        }
        $kind = $request->query('kind');
        $kind = isset(config('snl.card_kinds')[$kind]) ? $kind : null;

        $cards = Card::where('mode', $mode)
            ->when($kind, fn ($q) => $q->where('kind', $kind))
            ->orderBy('kind')->orderBy('intensity')->orderBy('id')->get();

        $counts = Card::selectRaw('mode, kind, count(*) as n')->where('is_active', true)->groupBy('mode', 'kind')->get()
            ->groupBy('mode')->map(fn ($g) => $g->pluck('n', 'kind'));

        return view('gameset.cards', [
            'mode' => $mode, 'kind' => $kind, 'cards' => $cards, 'counts' => $counts,
            'modes' => config('snl.modes'), 'kinds' => config('snl.card_kinds'),
            'levels' => config('snl.intensities'), 'boards' => Board::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Card::create($this->validated($request) + ['is_active' => true]);

        return back()->with('success', 'Card added.');
    }

    /** One card per line, all sharing the kind / mode / intensity chosen above. */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'kind'      => 'required|in:' . implode(',', array_keys(config('snl.card_kinds'))),
            'mode'      => 'required|in:' . implode(',', array_keys(config('snl.modes'))),
            'intensity' => 'required|integer|min:1|max:3',
            'board_id'  => 'nullable|integer|exists:boards,id',
            'lines'     => 'required|string|max:20000',
        ]);
        $added = 0;
        foreach (preg_split('/\R/u', $data['lines']) as $line) {
            $text = trim($line);
            if ($text === '' || mb_strlen($text) > 220) {
                continue;
            }
            Card::firstOrCreate(
                ['kind' => $data['kind'], 'mode' => $data['mode'], 'text' => $text, 'board_id' => $data['board_id'] ?? null],
                ['intensity' => $data['intensity'], 'is_active' => true]
            );
            $added++;
        }

        return back()->with('success', "{$added} card(s) added.");
    }

    public function update(Request $request, Card $card)
    {
        $card->update($this->validated($request) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Card saved.');
    }

    public function destroy(Card $card)
    {
        $card->delete();

        return back()->with('success', 'Card deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'kind'      => 'required|in:' . implode(',', array_keys(config('snl.card_kinds'))),
            'mode'      => 'required|in:' . implode(',', array_keys(config('snl.modes'))),
            'intensity' => 'required|integer|min:1|max:3',
            'board_id'  => 'nullable|integer|exists:boards,id',
            'text'      => 'required|string|max:220',
        ]);
        $data['board_id'] = $data['board_id'] ?? null;

        return $data;
    }
}
