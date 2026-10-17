<?php

namespace App\Console\Commands;

use App\Support\OfflinePack;
use Illuminate\Console\Command;

class BuildOfflinePack extends Command
{
    protected $signature = 'snl:pack {path? : where to write the JSON (default storage/app/offline-pack.json)}';

    protected $description = 'Write the offline game pack (rules config, boards, decks, words) used by the in-browser engine and the Android app.';

    public function handle(): int
    {
        $path = $this->argument('path') ?: storage_path('app/offline-pack.json');
        @mkdir(dirname($path), 0777, true);
        $pack = OfflinePack::build();
        file_put_contents($path, json_encode($pack, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->info(sprintf('Pack %s: %d boards, %d cards, %d words → %s (%s KB)', $pack['version'], count($pack['boards']), count($pack['cards']), count($pack['words']), $path, number_format(filesize($path) / 1024, 0)));

        return self::SUCCESS;
    }
}
