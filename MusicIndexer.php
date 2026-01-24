<?php

require __DIR__ . '/vendor/autoload.php';   // loads Laravel and Composer
require __DIR__ . '/bootstrap/app.php';     // bootstraps Laravel application

// Now you can use your models and controllers
use App\Http\Controllers\MusicApiController;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;

// Run the indexer
$musicApi = new MusicApiController();
$stored = $musicApi->fetchAndStoreAll(50, 20); // 50 tracks per page, 20 pages

echo "Indexed " . count($stored) . " tracks.\n";
