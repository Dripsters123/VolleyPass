<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SportDevsService;

class SyncUpcomingMatches extends Command
{
    protected $signature = 'volleyball:sync-upcoming';
    protected $description = 'Sync upcoming volleyball matches to the DB';

    public function handle(SportDevsService $api)
    {
        $api->syncUpcomingMatchesToDb();
        $this->info('Upcoming matches synced to the database.');
    }
}
