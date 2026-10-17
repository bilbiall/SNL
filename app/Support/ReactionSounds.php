<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Meme / reaction sounds. Drop .mp3 files into public/audio/reactions (or into a sub-folder named after the moment,
 * e.g. reactions/snake/). The file NAME says when it fits: "mischievous-laugh.mp3" is picked when someone is picked to drink,
 * "comedic-fail.mp3" when a fact is wrong, "wow-wow.mp3" on a ladder … The word list per moment lives in
 * config('snl.reaction_sounds.categories'). A file can fit several moments; files that match nothing join the "random" pool.
 * Identical files (same bytes, e.g. "clap.mp3" and "clap(1).mp3") are only used once.
 */
class ReactionSounds
{
    public const DIR = 'audio/reactions';

    /** @return array<string, array<int, string>> moment => list of file URLs */
    public static function manifest(): array
    {
        $dir = public_path(self::DIR);
        if (!is_dir($dir)) {
            return [];
        }
        $stamp = md5(implode('|', array_map(fn ($f) => $f . filemtime($f), self::files($dir))));

        return Cache::remember('snl_reaction_sounds_' . $stamp, 600, fn () => self::build($dir));
    }

    /** @return array<int, string> every mp3 under the folder */
    private static function files(string $dir): array
    {
        $out = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $f) {
            if (strtolower($f->getExtension()) === 'mp3') {
                $out[] = $f->getPathname();
            }
        }
        sort($out);

        return $out;
    }

    private static function build(string $dir): array
    {
        $cats = (array) config('snl.reaction_sounds.categories', []);
        $out = array_fill_keys(array_keys($cats), []);
        $out['random'] = [];
        $seen = [];

        foreach (self::files($dir) as $path) {
            $hash = md5_file($path);
            if (isset($seen[$hash])) {
                continue; // an exact duplicate
            }
            $seen[$hash] = true;
            $rel = str_replace('\\', '/', substr($path, strlen(public_path()) + 1));
            $url = asset(implode('/', array_map('rawurlencode', explode('/', $rel))));

            $sub = strtolower(basename(dirname($path)));
            $slug = ' ' . preg_replace('/[^a-z0-9]+/', ' ', strtolower(pathinfo($path, PATHINFO_FILENAME))) . ' ';
            $matched = [];
            if (isset($cats[$sub])) { // a folder named after the moment wins
                $matched[] = $sub;
            } else {
                foreach ($cats as $cat => $words) {
                    foreach ($words as $w) {
                        if (str_contains($slug, ' ' . preg_replace('/[^a-z0-9]+/', ' ', strtolower($w)))) { // word-start match: "mischiev" hits "mischievous"
                            $matched[] = $cat;
                            break;
                        }
                    }
                }
            }
            if (!$matched) {
                $out['random'][] = $url;
            }
            foreach ($matched as $cat) {
                $out[$cat][] = $url;
            }
        }

        return $out;
    }
}
