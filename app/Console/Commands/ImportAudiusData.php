<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;
use App\Services\AudiusService;
use Illuminate\Console\Command;

class ImportAudiusData extends Command
{
    protected $signature = 'audius:import {--limit=50 : Number of trending tracks to discover artists from}';
    protected $description = 'Import artists and ALL their tracks from Audius';

    public function handle(AudiusService $audiusService)
    {
        $this->info('Starting Audius import...');
        
        $limit = (int) $this->option('limit');
        
        // Step 1: Get trending tracks to discover artists
        $this->info('Fetching trending tracks to discover artists...');
        $trendingTracks = $audiusService->getTrendingTracks($limit);
        
        if (empty($trendingTracks)) {
            $this->error('No tracks found. Check your internet connection or Audius API status.');
            return 1;
        }
        
        // Step 2: Extract unique artists from trending tracks
        $artistIds = [];
        foreach ($trendingTracks as $track) {
            if (isset($track['user']['id'])) {
                $artistIds[$track['user']['id']] = $track['user'];
            }
        }
        
        $this->info('Found ' . count($artistIds) . ' unique artists');
        
        $progressBar = $this->output->createProgressBar(count($artistIds));
        $progressBar->start();

        $totalTracksImported = 0;

        // Step 3: For each artist, import ALL their tracks
        foreach ($artistIds as $artistId => $audiusArtist) {
            try {
                // Create or update artist
                $artist = Artist::updateOrCreate(
                    ['id' => $artistId],
                    [
                        'name' => $audiusArtist['name'] ?? 'Unknown Artist',
                        'nationality' => $audiusArtist['location'] ?? null,
                        'image' => $audiusService->getProfilePictureUrl($audiusArtist['profile_picture'] ?? null),
                        'description' => $audiusArtist['bio'] ?? null,
                    ]
                );

                // Fetch ALL tracks for this artist
                $allArtistTracks = $audiusService->getArtistTracks($artistId, 100); // Get up to 100 tracks per artist
                
                if (empty($allArtistTracks)) {
                    $progressBar->advance();
                    continue;
                }

                // Import all tracks for this artist
                foreach ($allArtistTracks as $track) {
                    try {
                        // Create album
                        $albumName = $track['title'] ?? 'Singles';
                        $releaseDate = $track['release_date'] ?? $track['created_at'] ?? now();
                        $year = date('Y', strtotime($releaseDate));

                        $album = Album::firstOrCreate(
                            [
                                'name' => $albumName,
                                'artist_id' => $artist->id,
                            ],
                            [
                                'cover' => $audiusService->getImageUrl($track['artwork'] ?? null),
                                'year' => $year,
                                'genre' => $track['genre'] ?? 'Unknown',
                            ]
                        );

                        // Create song
                        Song::updateOrCreate(
                            ['id' => $track['id']],
                            [
                                'name' => $track['title'] ?? 'Untitled',
                                'lyrics' => $track['description'] ?? null,
                                'album_id' => $album->id,
                                'stream_url' => $audiusService->getTrackStreamUrl($track['id']),
                            ]
                        );

                        $totalTracksImported++;
                    } catch (\Exception $e) {
                        $this->error("\nError importing track: " . $e->getMessage());
                        continue;
                    }
                }

                $progressBar->advance();
                
                // Small delay to avoid rate limiting
                usleep(100000); // 0.1 second delay
                
            } catch (\Exception $e) {
                $this->error("\nError importing artist: " . $e->getMessage());
                $progressBar->advance();
                continue;
            }
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info('✅ Import completed successfully!');
        $this->info('📊 Artists imported: ' . count($artistIds));
        $this->info('🎵 Total tracks imported: ' . $totalTracksImported);
        
        return 0;
    }
}