<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Pack;
use Illuminate\Http\Request;

/**
 * GameSet → Packs: sponsored or themed card packs. A pack is a named set of cards with an optional sponsor
 * ("Presented by …" on each card), the modes it appears in, and the dates it is live (e.g. December, or a venue's night).
 */
class GamesetPackController extends Controller
{
    /** Card kinds a pack can hold (the ones that need no answer key or timer). */
    private const KINDS = ['self', 'pick', 'everyone', 'duel', 'heart', 'rule', 'groupgame', 'bluff', 'vote'];

    public function index()
    {
        $kinds = collect(config('snl.card_kinds'))->only(self::KINDS)->all();

        return view('gameset.packs', [
            'packs' => Pack::withCount('cards')->with(['cards' => fn ($q) => $q->orderBy('kind')->orderBy('id')])->orderByDesc('id')->get(),
            'modes' => config('snl.modes'), 'levels' => config('snl.intensities'), 'kinds' => $kinds,
        ]);
    }

    public function store(Request $request)
    {
        $pack = Pack::create($this->validated($request) + ['is_active' => true]);
        $this->saveLogo($request, $pack);

        return redirect()->route('gameset.packs.index')->with('success', "Pack \"{$pack->name}\" created. Add its cards below.");
    }

    public function update(Request $request, Pack $pack)
    {
        $pack->update($this->validated($request) + ['is_active' => $request->boolean('is_active')]);
        $this->saveLogo($request, $pack);

        return redirect()->route('gameset.packs.index')->with('success', 'Pack saved.');
    }

    public function destroy(Pack $pack)
    {
        $this->forgetLogo($pack);
        $pack->cards()->delete();
        $pack->delete();

        return redirect()->route('gameset.packs.index')->with('success', 'Pack deleted.');
    }

    /** One card per line, all with the same kind / mode / intensity. */
    public function addCards(Request $request, Pack $pack)
    {
        $d = $request->validate([
            'kind'      => 'required|in:' . implode(',', self::KINDS),
            'mode'      => 'required|in:' . implode(',', array_merge(array_keys(config('snl.modes')), ['all'])),
            'intensity' => 'required|integer|min:1|max:3',
            'lines'     => 'required|string|max:20000',
        ]);
        $n = 0;
        foreach (preg_split('/\R/u', $d['lines']) as $line) {
            $t = trim($line);
            if ($t === '' || mb_strlen($t) > 280) {
                continue;
            }
            $card = Card::firstOrCreate(
                ['pack_id' => $pack->id, 'kind' => $d['kind'], 'mode' => $d['mode'], 'text' => $t],
                ['intensity' => $d['intensity'], 'penalty' => 1, 'is_active' => true]
            );
            $n += $card->wasRecentlyCreated ? 1 : 0;
        }

        return redirect()->route('gameset.packs.index')->with('success', "$n card" . ($n === 1 ? '' : 's') . " added to \"{$pack->name}\".");
    }

    public function destroyCard(Pack $pack, Card $card)
    {
        abort_unless($card->pack_id === $pack->id, 404);
        $card->delete();

        return redirect()->route('gameset.packs.index')->with('success', 'Card removed.');
    }

    private function validated(Request $request): array
    {
        $d = $request->validate([
            'name'      => 'required|string|max:60',
            'sponsor'   => 'nullable|string|max:60',
            'tagline'   => 'nullable|string|max:120',
            'link'      => 'nullable|url|max:200',
            'modes'     => 'nullable|array',
            'modes.*'   => 'in:' . implode(',', array_keys(config('snl.modes'))),
            'starts_on' => 'nullable|date',
            'ends_on'   => 'nullable|date|after_or_equal:starts_on',
            'logo'      => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
        ]);
        unset($d['logo']);
        $d['modes'] = !empty($d['modes']) ? array_values($d['modes']) : null; // none ticked = every mode
        foreach (['sponsor', 'tagline', 'link'] as $k) {
            $d[$k] = isset($d[$k]) && trim($d[$k]) !== '' ? trim($d[$k]) : null;
        }

        return $d;
    }

    /** Sponsor logos live in public/uploads/packs (png / jpg / webp only — never svg). */
    private function saveLogo(Request $request, Pack $pack): void
    {
        $file = $request->file('logo');
        if (!$file) {
            return;
        }
        $dir = public_path('uploads/packs');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = 'pack-' . $pack->id . '-' . bin2hex(random_bytes(4)) . '.' . strtolower($file->guessExtension() ?: 'png');
        $file->move($dir, $name);
        $this->forgetLogo($pack);
        $pack->update(['logo' => '/uploads/packs/' . $name]);
    }

    private function forgetLogo(Pack $pack): void
    {
        if ($pack->logo && str_starts_with($pack->logo, '/uploads/packs/')) {
            @unlink(public_path($pack->logo));
        }
    }
}
