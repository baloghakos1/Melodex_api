<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\MusicImportService;
use App\Jobs\ImportAudiusTrack;

class AudiusController extends Controller
{
    private $import;

    public function __construct(MusicImportService $import)
    {
        $this->import = $import;
    }

    private function base()
    {
        return "https://discoveryprovider.audius.co/v1";
    }

    public function importTrack($id)
    {
        $response = Http::get($this->base() . "/tracks/$id", [
            'app_name' => 'Melodex'
        ])->json();

        // Debug: Log the response structure
        Log::info('Track API Response:', $response);

        // Check if response has 'data' key and it's an array
        if (isset($response['data'])) {
            $data = $response['data'];
            // If data has index 0, get it; otherwise use data directly
            $track = isset($data[0]) ? $data[0] : $data;
        } elseif (isset($response[0])) {
            // Response might be an array directly
            $track = $response[0];
        } else {
            // Response might be the track object directly
            $track = $response;
        }

        if (!$track) {
            return response()->json(['error' => 'Track not found'], 404);
        }

        $stream = Http::get($this->base() . "/tracks/$id/stream", [
            'app_name' => 'Melodex'
        ]);

        $normalized = [
            'artist_name' => $track['user']['name'],
            'artist_image' => $track['user']['profile_picture'] ?? null,
            'artist_nationality' => null,
            'artist_description' => null,

            'album_name' => $track['album'] ?? 'Singles',
            'album_cover' => $track['artwork']['150x150'] ?? null,
            'album_year' => date('Y', strtotime($track['release_date'] ?? now())),
            'album_genre' => $track['genre'] ?? 'Unknown',

            'track_name' => $track['title'],
            'lyrics' => null,
            'stream_url' => $stream->effectiveUri()
        ];

        $song = $this->import->importTrack($normalized);

        return response()->json($song);
    }

    public function importAll($query = 'trending')
    {
        // Get limit from query string, default to 50
        $limit = request()->get('limit', 50);
        
        // Fetch trending or search tracks
        $endpoint = $query === 'trending' 
            ? "/tracks/trending"
            : "/tracks/search";

        $params = [
            'app_name' => 'Melodex',
            'limit' => $limit,
        ];

        if ($query !== 'trending') {
            $params['query'] = request()->get('query', $query);
        }

        $response = Http::get($this->base() . $endpoint, $params)->json();

        if (!isset($response['data']) || empty($response['data'])) {
            return response()->json(['error' => 'No tracks found'], 404);
        }

        $tracks = $response['data'];
        $queued = 0;

        // Dispatch each track import as a background job
        foreach ($tracks as $track) {
            ImportAudiusTrack::dispatch($track['id'], $track);
            $queued++;
        }

        return response()->json([
            'message' => 'Import started',
            'queued' => $queued,
            'note' => 'Tracks are being imported in the background. Check logs for progress.'
        ]);
    }

    private function importTrackData($id, $trackData = null)
    {
        // If track data not provided, fetch it
        if (!$trackData) {
            $response = Http::get($this->base() . "/tracks/$id", [
                'app_name' => 'Melodex'
            ])->json();

            if (isset($response['data'])) {
                $data = $response['data'];
                $trackData = isset($data[0]) ? $data[0] : $data;
            } elseif (isset($response[0])) {
                $trackData = $response[0];
            } else {
                $trackData = $response;
            }
        }

        if (!$trackData) {
            throw new \Exception('Track not found');
        }

        $stream = Http::get($this->base() . "/tracks/$id/stream", [
            'app_name' => 'Melodex'
        ]);

        $normalized = [
            'artist_name' => $trackData['user']['name'],
            'artist_image' => $trackData['user']['profile_picture'] ?? null,
            'artist_nationality' => null,
            'artist_description' => null,

            'album_name' => $trackData['album'] ?? 'Singles',
            'album_cover' => $trackData['artwork']['150x150'] ?? null,
            'album_year' => date('Y', strtotime($trackData['release_date'] ?? now())),
            'album_genre' => $trackData['genre'] ?? 'Unknown',

            'track_name' => $trackData['title'],
            'lyrics' => null,
            'stream_url' => $stream->effectiveUri()
        ];

        return $this->import->importTrack($normalized);
    }
}