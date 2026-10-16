<?php

use Illuminate\Support\Facades\Schedule;

// Housekeeping: finished/abandoned rooms are purged so the table never grows forever.
Schedule::command('snl:purge-rooms')->dailyAt('04:00');
