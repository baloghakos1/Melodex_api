<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;

class MusicImportService
{
    public function importTrack(array $data)
    {
        // 1. Artist
        $artist = Artist::firstOrCreate(
            ['name' => $data['artist_name']],
            [
                'nationality' => $this->ensureString($data['artist_nationality']),
                'image' => $this->ensureString($data['artist_image']),
                'description' => $this->ensureString($data['artist_description']),
            ]
        );

        // 2. Album
        $album = Album::firstOrCreate(
            [
                'name' => $data['album_name'],
                'artist_id' => $artist->id
            ],
            [
                'cover' => $this->ensureString($data['album_cover']),
                'year' => $data['album_year'] ?? now()->year,
                'genre' => $data['album_genre'] ?? 'Unknown'
            ]
        );

        // 3. Song
        $song = Song::firstOrCreate(
            [
                'name' => $data['track_name'],
                'album_id' => $album->id
            ],
            [
                'lyrics' => $this->ensureString($data['lyrics']),
                'stream_url' => $data['stream_url']
            ]
        );

        return $song;
    }

    private function ensureString($value)
    {
        if (is_array($value)) {
            return isset($value[0]) ? (string)$value[0] : null;
        }
        return $value ? (string)$value : null;
    }
}
