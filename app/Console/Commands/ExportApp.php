<?php

namespace App\Console\Commands;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OfflineController;
use App\Support\OfflinePack;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Bakes the party-mode game into plain static files (mobile/www) that the Android app bundles —
 * so the installed app opens and plays with no network, even on its very first launch.
 *   php artisan snl:export-app            (then: npm run apk)
 */
class ExportApp extends Command
{
    protected $signature = 'snl:export-app {dir? : output folder (default mobile/www)}';

    protected $description = 'Export the offline party game as static files for the Android app.';

    public function handle(): int
    {
        $out = $this->argument('dir') ?: base_path('mobile/www');
        $this->wipe($out);
        @mkdir($out, 0777, true);

        config(['session.driver' => 'array']);
        URL::forceRootUrl('https://localhost');
        URL::forceScheme('https');
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        $home = app(HomeController::class);
        $pages = [
            'index.html' => $home->index()->render(),
            'setup.html' => $home->create(Request::create('/new', 'GET', ['type' => 'party']))->render(),
            'play.html'  => app(OfflineController::class)->local()->render(),
        ];
        foreach ($pages as $file => $html) {
            file_put_contents("$out/$file", $this->localise($html));
        }

        // which version this app is, and where to look for a newer one
        $ver = json_decode(file_get_contents(base_path('mobile/app-version.json')), true) ?: [];
        file_put_contents("$out/app-info.json", json_encode(['versionName' => $ver['versionName'] ?? '1.0.0', 'versionCode' => (int) ($ver['versionCode'] ?? 1), 'site' => rtrim(config('snl.public_url'), '/')]));

        // the game pack (rules, boards, decks, words) — read by the in-app engine
        $pack = OfflinePack::build();
        file_put_contents("$out/pack.json", json_encode($pack, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        // static files the pages need
        foreach (['js', 'css', 'fonts', 'icons', 'avatars', 'audio', 'img/landing', 'img/boards', 'build/assets'] as $dir) {
            $this->copyDir(public_path($dir), "$out/$dir");
        }
        foreach (['icons.svg', 'favicon.ico', 'manifest.webmanifest', 'img/logo-96.png', 'build/manifest.json'] as $file) {
            if (is_file(public_path($file))) {
                @mkdir(dirname("$out/$file"), 0777, true);
                copy(public_path($file), "$out/$file");
            }
        }
        // the other game's loop and the service worker aren't part of the app
        @unlink("$out/audio/map-loop.mp3");
        @unlink("$out/js/pwa.js.map");

        $this->info(sprintf('Exported %d pages + %d files → %s (pack %s, %s MB)', count($pages), $this->countFiles($out), $out, $pack['version'], number_format($this->size($out) / 1048576, 1)));

        return self::SUCCESS;
    }

    /** Rewrites server URLs into the static app's own files and removes the parts that need a server (online play, admin). */
    private function localise(string $html): string
    {
        // online play / public games stay, but point at the live site and only show when the device is online
        $site = rtrim(config('snl.public_url'), '/');
        $html = preg_replace_callback('#<a ([^>]*)href="https://localhost((?:/new\?type=online|/rooms)[^"]*)"([^>]*)>#', fn ($m) => '<a data-online style="display:none" ' . $m[1] . 'href="' . $site . html_entity_decode($m[2]) . '"' . $m[3] . '>', $html);
        // anchors that lead to admin / downloads / content pages don't belong in the offline app
        $html = preg_replace('#<a (?![^>]*data-online)[^>]*href="[^"]*(?:type=online|/login|/join|/app|/download|/rooms|/how-to-play|/fun-facts)[^"]*"[^>]*>.*?</a>#s', '', $html);
        $reveal = '<script>(function(){var l=[].slice.call(document.querySelectorAll("a[data-online]"));if(!l.length||!navigator.onLine)return;var c=new AbortController(),t=setTimeout(function(){c.abort()},4000);fetch(' . json_encode($site . '/favicon.ico') . ',{mode:"no-cors",cache:"no-store",signal:c.signal}).then(function(){clearTimeout(t);l.forEach(function(a){a.style.display=""});}).catch(function(){});})();</script>';
        $html = str_replace('</body>', $reveal . '</body>', $html);
        $html = preg_replace('#<section class="getapp".*?</section>#s', '', $html);
        $html = preg_replace('#<section class="about".*?</section>#s', '', $html);
        $html = preg_replace('#<div class="share-row".*?</div>#s', '', $html);

        $pairs = [
            'https://localhost/offline/pack.json' => '/pack.json',
            'https://localhost/new?type=party' => '/setup.html',
            'https://localhost/play/local' => '/play.html',
        ];
        foreach ($pairs as $from => $to) {
            $html = str_replace([$from, str_replace('/', '\/', $from)], [$to, str_replace('/', '\/', $to)], $html);
        }
        // home links and everything else under the forced root become root-relative
        $html = str_replace(['"https://localhost"', '"https:\/\/localhost"'], ['"/index.html"', '"\/index.html"'], $html);
        $html = str_replace(['"https://localhost/"', '"https:\/\/localhost\/"'], ['"/index.html"', '"\/index.html"'], $html);
        $html = str_replace(['https://localhost', 'https:\/\/localhost'], '', $html);

        return $html;
    }

    private function wipe(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
    }

    private function copyDir(string $from, string $to): void
    {
        if (!is_dir($from)) {
            return;
        }
        @mkdir($to, 0777, true);
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $f) {
            $target = $to . DIRECTORY_SEPARATOR . substr($f->getPathname(), strlen($from) + 1);
            if ($f->isDir()) {
                @mkdir($target, 0777, true);
            } else {
                copy($f->getPathname(), $target);
            }
        }
    }

    private function countFiles(string $dir): int
    {
        $n = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $f) {
            $n++;
        }

        return $n;
    }

    private function size(string $dir): int
    {
        $s = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $f) {
            $s += $f->getSize();
        }

        return $s;
    }
}
