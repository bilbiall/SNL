<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Response;

/**
 * Indexable content pages (how to play, fun facts), the sitemap and robots.txt.
 * Written for people first: plain rules, honest descriptions, family-friendly facts — nothing that glamorises getting drunk.
 */
class ContentController extends Controller
{
    public function howToPlay()
    {
        $kinds = collect(config('snl.tile_kinds'))->except(['blank', 'snake_head', 'ladder_bottom'])->all();

        return view('how-to-play', ['kinds' => $kinds, 'modes' => config('snl.modes'), 'levels' => config('snl.intensities')]);
    }

    public function funFacts()
    {
        // family-safe true/false facts only (no drinks, no adult themes)
        $bad = ['alcohol', 'drunk', 'hangover', 'shot', 'wine', 'beer', 'vodka', 'whisk', 'tequila', 'champagne', 'cocktail', 'kiss', 'dating', 'love'];
        $facts = Card::where('kind', 'fact')->where('is_active', true)->where('intensity', '<=', 2)->whereNotNull('answer')->orderBy('id')->get()
            ->reject(fn ($c) => collect($bad)->contains(fn ($w) => str_contains(mb_strtolower($c->text . ' ' . $c->explain), $w)))
            ->values();

        return view('fun-facts', ['facts' => $facts]);
    }

    public function about()
    {
        return view('about', ['email' => config('snl.contact_email')]);
    }

    public function privacy()
    {
        return view('privacy', ['email' => config('snl.contact_email'), 'updated' => '18 October 2026']);
    }

    public function sitemap(): Response
    {
        $today = now()->toDateString();
        $urls = [
            [url('/'), '1.0', 'weekly'], [route('how-to-play'), '0.8', 'monthly'], [route('fun-facts'), '0.8', 'monthly'],
            [route('rooms.index'), '0.6', 'hourly'], [route('app.page'), '0.6', 'monthly'],
            [route('about'), '0.5', 'monthly'], [route('privacy'), '0.3', 'yearly'],
        ];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$loc, $prio, $freq]) {
            $xml .= "  <url><loc>" . e($loc) . "</loc><lastmod>{$today}</lastmod><changefreq>{$freq}</changefreq><priority>{$prio}</priority></url>\n";
        }

        return response($xml . '</urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $txt = "User-agent: Mediapartners-Google\nAllow: /\n\nUser-agent: *\nAllow: /\nDisallow: /gameset\nDisallow: /login\nDisallow: /play/\nDisallow: /new\nDisallow: /join\nDisallow: /offline/\nDisallow: /download/\n\nSitemap: " . url('/sitemap.xml') . "\n";

        return response($txt, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** ads.txt: tells ad buyers this AdSense account may sell the site's ad space. 404 until SNL_ADSENSE_ID is set. */
    public function adsTxt(): Response
    {
        $id = (string) config('snl.ads.client');
        abort_unless(preg_match('/^ca-pub-\d+$/', $id), 404);

        return response('google.com, ' . substr($id, 3) . ", DIRECT, f08c47fec0942fa0\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
