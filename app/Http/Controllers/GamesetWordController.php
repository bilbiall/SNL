<?php

namespace App\Http\Controllers;

use App\Models\WordList;
use Illuminate\Http\Request;

/**
 * GameSet → Words: the slot words behind templated cards. A card that says
 * "Recommend a movie for {scene}" draws a fresh {scene} word every time it's played.
 */
class GamesetWordController extends Controller
{
    /** Slots the starter cards already use — shown as hints; admins can add any new slot name. */
    private const KNOWN = [
        'scene' => 'A situation or moment ("a rainy Sunday")',
        'moment' => 'A shorter moment ("the drive home")',
        'vibe' => 'A mood or energy ("main character energy")',
        'hot_topic' => 'A hot take to defend',
        'rando' => 'Something to rate out of 10',
        'topic' => 'A subject for a "fact"',
        'genre' => 'A movie genre',
        'decade' => 'A decade',
    ];

    public function index(Request $request)
    {
        $slots = WordList::select('slot')->distinct()->pluck('slot')->merge(array_keys(self::KNOWN))->unique()->sort()->values();
        $slot = in_array($request->query('slot'), $slots->all(), true) ? $request->query('slot') : $slots->first();
        $counts = WordList::selectRaw('slot, count(*) n')->where('is_active', true)->groupBy('slot')->pluck('n', 'slot');

        return view('gameset.words', [
            'slots' => $slots, 'slot' => $slot, 'counts' => $counts, 'known' => self::KNOWN,
            'words' => WordList::where('slot', $slot)->orderBy('intensity')->orderBy('id')->get(),
            'modes' => config('snl.modes') + ['all' => ['label' => 'All modes']],
            'levels' => config('snl.intensities'),
        ]);
    }

    public function store(Request $request)
    {
        $d = $this->validated($request);
        WordList::create($d + ['is_active' => true]);

        return redirect()->route('gameset.words.index', ['slot' => $d['slot']])->with('success', 'Word added.');
    }

    /** One word per line. */
    public function bulk(Request $request)
    {
        $d = $request->validate([
            'slot'      => ['required', 'regex:/^[a-z_]{2,24}$/'],
            'mode'      => 'required|in:' . implode(',', array_merge(array_keys(config('snl.modes')), ['all'])),
            'intensity' => 'required|integer|min:1|max:3',
            'lines'     => 'required|string|max:20000',
        ]);
        $n = 0;
        foreach (preg_split('/\R/u', $d['lines']) as $line) {
            $t = trim($line);
            if ($t === '' || mb_strlen($t) > 90) {
                continue;
            }
            WordList::firstOrCreate(['slot' => $d['slot'], 'text' => $t], ['mode' => $d['mode'], 'intensity' => $d['intensity'], 'is_active' => true]);
            $n++;
        }

        return redirect()->route('gameset.words.index', ['slot' => $d['slot']])->with('success', "{$n} word(s) added.");
    }

    public function update(Request $request, WordList $word)
    {
        $word->update($this->validated($request) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Word saved.');
    }

    public function destroy(WordList $word)
    {
        $word->delete();

        return back()->with('success', 'Word deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'slot'      => ['required', 'regex:/^[a-z_]{2,24}$/'],
            'mode'      => 'required|in:' . implode(',', array_merge(array_keys(config('snl.modes')), ['all'])),
            'intensity' => 'required|integer|min:1|max:3',
            'text'      => 'required|string|max:90',
        ]);
    }
}
