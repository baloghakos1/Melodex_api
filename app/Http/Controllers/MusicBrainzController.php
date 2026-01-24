<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\MusicImportService;

class MusicBrainzController extends Controller
{
    private $import;

    public function __construct(MusicImportService $import)
    {
        $this->import = $import;
    }

    private function base()
    {
        return "https://musicbrainz.org/ws/2";
    }

    private function headers()
    {
        return [
            'User-Agent' => 'YourApp/1.0 (your@email.com)'
        ];
    }

    public function importRecording($id)
    {
        $recording = Http::withHeaders($this->headers())
            ->get($this->base() . "/recording/$id", [
                'inc' => 'artists+releases',
                'fmt' => 'json'
            ])
            ->json();

        $artist = $recording['artist-credit'][0]['artist'];

        $release = $recording['releases'][0] ?? null;

        $normalized = [
            'artist_name' => $artist['name'],
            'artist_image' => null,
            'artist_nationality' => null,
            'artist_description' => null,

            'album_name' => $release['title'] ?? 'Unknown',
            'album_cover' => null,
            'album_year' => $release['date'] ?? now()->year,
            'album_genre' => 'Unknown',

            'track_name' => $recording['title'],
            'lyrics' => null,
            'stream_url' => null // MusicBrainz has no audio
        ];

        $song = $this->import->importTrack($normalized);

        return response()->json($song);
    }
}
