<?php

namespace App\Support;

use App\Models\Board;

/**
 * Every URL the service worker should keep on the device so the game runs with no network:
 * the pages that make up party mode, scripts/styles/fonts, icons, avatars, board art and the sound clips.
 * Served as /offline/assets.json; `version` changes whenever any listed file changes, which tells installed apps to refresh.
 */
class OfflineAssets
{
    /** Pages that must work offline (server-rendered once, then replayed from cache). */
    public const PAGES = ['/', '/new?type=party', '/play/local', '/offline.html'];

    /** Same cache-buster the Blade pages use for a script. */
    public static function js(string $file): string
    {
        $p = public_path("js/$file");

        return asset("js/$file") . '?v=' . (is_file($p) ? filemtime($p) : 0);
    }

    public static function build(): array
    {
        $urls = [];
        $add = function (string $path) use (&$urls) {
            if (is_file(public_path($path))) {
                $urls[] = '/' . ltrim($path, '/');
            }
        };

        foreach (glob(public_path('js/*.js')) as $f) {
            $urls[] = '/js/' . basename($f) . '?v=' . filemtime($f);
        }
        foreach (['icons.svg', 'favicon.ico', 'manifest.webmanifest', 'css/fonts.css', 'img/logo-96.png'] as $p) {
            $add($p);
        }
        foreach (glob(public_path('fonts/*.woff2')) as $f) {
            $add('fonts/' . basename($f));
        }
        foreach (glob(public_path('icons/*.png')) as $f) {
            $add('icons/' . basename($f));
        }
        foreach (glob(public_path('avatars/*.svg')) as $f) {
            $add('avatars/' . basename($f));
        }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(public_path('audio'), \FilesystemIterator::SKIP_DOTS)) as $f) {
            if (strtolower($f->getExtension()) === 'mp3' && $f->getFilename() !== 'map-loop.mp3') { // map-loop belongs to the other game
                $add(str_replace('\\', '/', substr($f->getPathname(), strlen(public_path()) + 1)));
            }
        }
        foreach (['img/landing', 'img/boards'] as $dir) {
            foreach (glob(public_path("$dir/*")) as $f) {
                $add("$dir/" . basename($f));
            }
        }
        // boards uploaded through GameSet live under /uploads (or are absolute URLs)
        foreach (Board::playable() as $b) {
            $img = $b->board_image ?: config('snl.default_board_art');
            if ($img && !preg_match('#^https?://#', $img)) {
                $add($img);
            }
        }
        // Vite build (hashed, so exact URLs are safe to keep forever)
        $manifest = public_path('build/manifest.json');
        if (is_file($manifest)) {
            foreach ((array) json_decode(file_get_contents($manifest), true) as $entry) {
                foreach (array_merge([$entry['file'] ?? null], $entry['css'] ?? [], $entry['assets'] ?? []) as $f) {
                    if ($f) {
                        $urls[] = '/build/' . $f;
                    }
                }
            }
        }

        $urls = array_values(array_unique($urls));
        sort($urls);
        $stamp = md5(json_encode($urls) . OfflinePack::build()['version']);

        return [
            'version' => substr($stamp, 0, 12),
            'pages'   => self::PAGES,
            'pack'    => '/offline/pack.json',
            'urls'    => $urls,
        ];
    }
}
