<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;

class PurgeRooms extends Command
{
    protected $signature = 'snl:purge-rooms';

    protected $description = 'Delete rooms (and their players) that have been idle for longer than the retention window.';

    public function handle(): int
    {
        $days = (int) config('snl.timing.room_keep_days', 3);
        $n = Room::where('updated_at', '<', now()->subDays($days))->delete(); // players cascade
        $this->info("Purged {$n} idle room(s).");

        return self::SUCCESS;
    }
}
