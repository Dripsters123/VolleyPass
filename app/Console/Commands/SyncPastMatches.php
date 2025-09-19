<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SportDevsService;

class SyncPastMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'volleyball:sync-past';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync past volleyball matches to the DB';

    protected $service;
    /**
     * Execute the console command.
     */
    public function __construct(SportDevsService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

     public function handle(SportDevsService $api)
    {
        $api->syncPastMatchesToDb();
        $this->info('Past matches synced to the database.');
    }
}
