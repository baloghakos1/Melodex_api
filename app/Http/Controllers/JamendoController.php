<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\MusicImportService;

class JamendoController extends Controller
{
    private $import;

    public function __construct(MusicImportService $import)
    {
        $this->import = $import;
    }

    private function base()
    {
        return "https://api.jamendo.com/v3.0";
    }

    private function auth()
    {
        return ['client_id' => env('JAMENDO_CLIENT_ID')];
    }

    public function importTrack($id)
    {
        $response = Http::get($this->base() . "/tracks", array_merge($this->auth(), [
            'id' => $id,
            'audioformat' => 'mp31',
            'include' => 'musicinfo+stats'
        ]));

        $track = $response->json()['results'][0];

        $normalized = [
            'artist_name' => $track['artist_name'],
            'artist_image' => $track['image'] ?? null,
            'artist_nationality' => null,
            'artist_description' => null,

            'album_name' => $track['album_name'] ?? 'Singles',
            'album_cover' => $track['album_image'] ?? null,
            'album_year' => $track['releasedate'] ? date('Y', strtotime($track['releasedate'])) : now()->year,
            'album_genre' => $track['musicinfo']['tags']['genres'][0] ?? 'Unknown',

            'track_name' => $track['name'],
            'lyrics' => null,
            'stream_url' => $track['audio']
        ];

        $song = $this->import->importTrack($normalized);

        return response()->json($song);
    }
}
