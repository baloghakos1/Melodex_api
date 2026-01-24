<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\MusicImportService;

class ImportAudiusTrack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trackId;
    protected $trackData;

    /**
     * Create a new job instance.
     */
    public function __construct($trackId, $trackData)
    {
        $this->trackId = $trackId;
        $this->trackData = $trackData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $import = new MusicImportService();
            
            // Log the full track data to see what we're getting
            Log::info("Track data received: " . json_encode($this->trackData));
            
            $stream = Http::get($this->base() . "/tracks/{$this->trackId}/stream", [
                'app_name' => 'Melodex'
            ]);

            $normalized = [
                'artist_name' => $this->trackData['user']['name'],
                'artist_image' => $this->trackData['user']['profile_picture']['150x150'] ?? null,
                'artist_nationality' => $this->trackData['user']['location'] ?? null,
                'artist_description' => $this->trackData['user']['bio'] ?? null,

                'album_name' => $this->trackData['album'] ?? 'Singles',
                'album_cover' => $this->trackData['artwork']['150x150'] ?? null,
                'album_year' => date('Y', strtotime($this->trackData['release_date'] ?? now())),
                'album_genre' => $this->trackData['genre'] ?? 'Unknown',

                'track_name' => $this->trackData['title'],
                'lyrics' => null,
                'stream_url' => $stream->effectiveUri()
            ];

            Log::info("Normalized data: " . json_encode($normalized));
            
            $import->importTrack($normalized);
            Log::info("Track imported: {$this->trackData['title']}");
        } catch (\Exception $e) {
            Log::error("Failed to import track {$this->trackId}: {$e->getMessage()}");
        }
    }

    private function base()
    {
        return "https://discoveryprovider.audius.co/v1";
    }
}
