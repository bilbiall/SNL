<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use Illuminate\Http\Request;

/**
 * GameSet → Cards: the decks. Kind × content mode × intensity, with a bulk "one card per line" adder.
 * Modes include "all" — cards that work in every mode (facts, prompts, anything with no drink in it).
 */
class GamesetCardController extends Controller
{
    private function modeKeys(): array
    {
        return array_merge(array_keys(config('snl.modes')), ['all']);
    }

    public function index(Request $request)
    {
        $mode = in_array($request->query('mode'), $this->modeKeys(), true) ? $request->query('mode') : 'all';
        $kind = $request->query('kind');
        $kind = isset(config('snl.card_kinds')[$kind]) ? $kind : null;

        $cards = Card::where('mode', $mode)
            ->when($kind, fn ($q) => $q->where('kind', $kind))
            ->orderBy('kind')->orderBy('intensity')->orderBy('id')->get();

        $counts = Card::selectRaw('mode, kind, count(*) as n')->where('is_active', true)->groupBy('mode', 'kind')->get()
            ->groupBy('mode')->map(fn ($g) => $g->pluck('n', 'kind'));

        return view('gameset.cards', [
            'mode' => $mode, 'kind' => $kind, 'cards' => $cards, 'counts' => $counts,
            'modes' => config('snl.modes') + ['all' => ['label' => 'All modes', 'icon' => 'cards', 'enabled' => true]],
            'kinds' => config('snl.card_kinds'),
            'levels' => config('snl.intensities'), 'boards' => Board::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Card::create($this->validated($request) + ['is_active' => true]);

        return back()->with('success', 'Card added.');
    }

    /**
     * One card per line. For True-or-False cards start the line with T| or F| and optionally add an
     * explanation after a second bar:   T | Octopuses have three hearts. | Two pump blood to the gills.
     */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'kind'      => 'required|in:' . implode(',', array_keys(config('snl.card_kinds'))),
            'mode'      => 'required|in:' . implode(',', $this->modeKeys()),
            'intensity' => 'required|integer|min:1|max:3',
            'penalty'   => 'nullable|integer|min:1|max:2',
            'timer'     => 'nullable|integer|min:5|max:90',
            'board_id'  => 'nullable|integer|exists:boards,id',
            'lines'     => 'required|string|max:30000',
        ]);
        $added = 0;
        $skipped = 0;
        foreach (preg_split('/\R/u', $data['lines']) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $extra = [];
            if ($data['kind'] === 'fact') {
                if (!preg_match('/^([TtFf])\s*\|\s*(.+?)(?:\s*\|\s*(.+))?$/u', $line, $m)) {
                    $skipped++;   // a True-or-False card needs its answer — skip rather than guess
                    continue;
                }
                $line = trim($m[2]);
                $extra = ['answer' => strtolower($m[1]) === 't', 'explain' => isset($m[3]) ? mb_substr(trim($m[3]), 0, 220) : null];
            }
            if (mb_strlen($line) > 220) {
                $skipped++;
                continue;
            }
            Card::firstOrCreate(
                ['kind' => $data['kind'], 'mode' => $data['mode'], 'text' => $line, 'board_id' => $data['board_id'] ?? null],
                ['intensity' => $data['intensity'], 'is_active' => true, 'penalty' => $data['penalty'] ?? 1, 'timer' => in_array($data['kind'], ['spotlight', 'quickfire'], true) ? ($data['timer'] ?? 15) : null] + $extra
            );
            $added++;
        }

        return back()->with('success', "{$added} card(s) added." . ($skipped ? " {$skipped} line(s) skipped — True-or-False lines need a T| or F| prefix." : ''));
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
            'mode'      => 'required|in:' . implode(',', $this->modeKeys()),
            'intensity' => 'required|integer|min:1|max:3',
            'board_id'  => 'nullable|integer|exists:boards,id',
            'text'      => 'required|string|max:220',
            'answer'    => 'nullable|in:true,false',
            'explain'   => 'nullable|string|max:220',
            'penalty'   => 'nullable|integer|min:1|max:2',
            'timer'     => 'nullable|integer|min:5|max:90',
        ]);
        if ($data['kind'] === 'fact' && !isset($data['answer'])) {
            abort(back()->withInput()->withErrors(['A True-or-False card needs an answer — pick True or False.']));
        }
        $data['board_id'] = $data['board_id'] ?? null;
        $data['answer']   = $data['kind'] === 'fact' ? ($data['answer'] === 'true') : null;
        $data['explain']  = $data['kind'] === 'fact' ? ($data['explain'] ?? null) : null;
        $data['penalty']  = in_array($data['kind'], ['fact', 'bluff', 'spotlight', 'quickfire', 'vote'], true) ? ($data['penalty'] ?? 1) : 1;
        $data['timer']    = in_array($data['kind'], ['spotlight', 'quickfire'], true) ? ($data['timer'] ?? 15) : null;

        return $data;
    }
}
