<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MusicApiController;

class MusicIndexer extends Command
{
    protected $signature = 'music:index';
    protected $description = 'Fetch tracks from Audius and store in DB';

    public function handle()
    {
        $this->info('Starting Music Indexer...');
        $musicApi = new MusicApiController();
        $stored = $musicApi->fetchAndStoreAll(50, 20);

        $this->info('Indexed ' . count($stored) . ' tracks.');
    }
}
