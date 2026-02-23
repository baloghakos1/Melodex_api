<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AudiusService
{
    private array $hosts = [
        'https://discoveryprovider.audius.co',
        'https://discoveryprovider2.audius.co',
        'https://discoveryprovider3.audius.co',
    ];

    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
    }

    private function getHost(): string
    {
        return $this->hosts[array_rand($this->hosts)];
    }

    public function getTrendingTracks(int $limit = 100): array
    {
        try {
            $host = $this->getHost();
            $response = $this->client->get("{$host}/v1/tracks/trending", [
                'query' => ['limit' => $limit]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            return $body['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Audius API Exception: ' . $e->getMessage());
            return [];
        }
    }

    public function getArtistTracks(string $userId, int $limit = 50): array
    {
        try {
            $host = $this->getHost();
            $response = $this->client->get("{$host}/v1/users/{$userId}/tracks", [
                'query' => ['limit' => $limit]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            return $body['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Audius API Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getArtistAlbums(string $userId): array
    {
        try {
            $host = $this->getHost();
            $response = $this->client->get("{$host}/v1/users/{$userId}/albums");

            $body = json_decode($response->getBody()->getContents(), true);
            $albums = $body['data'] ?? [];

            // Only return albums that have at least one track
            return array_values(array_filter($albums, function ($album) {
                return isset($album['track_count']) && $album['track_count'] > 0;
            }));
        } catch (\Exception $e) {
            Log::error('Audius API Error (albums): ' . $e->getMessage());
            return [];
        }
    }

    public function getAlbumTracks(string $albumId): array
    {
        try {
            $host = $this->getHost();
            $response = $this->client->get("{$host}/v1/playlists/{$albumId}/tracks");

            $body = json_decode($response->getBody()->getContents(), true);
            return $body['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Audius API Error (album tracks): ' . $e->getMessage());
            return [];
        }
    }

    public function getTrackStreamUrl(string $trackId): string
    {
        $host = $this->getHost();
        return "{$host}/v1/tracks/{$trackId}/stream";
    }

    /**
     * Audius returns full image URLs directly in the artwork object.
     * e.g. $artwork['1000x1000'] = 'https://...'
     * Just return it directly — no need to rebuild the URL.
     */
    public function getImageUrl(?array $artwork, string $size = '1000x1000'): ?string
    {
        if (!$artwork) {
            return null;
        }

        // Return the size requested, fall back to smaller sizes
        return $artwork[$size]
            ?? $artwork['480x480']
            ?? $artwork['150x150']
            ?? null;
    }

    public function getProfilePictureUrl(?array $profilePicture, string $size = '1000x1000'): ?string
    {
        if (!$profilePicture) {
            return null;
        }

        return $profilePicture[$size]
            ?? $profilePicture['480x480']
            ?? $profilePicture['150x150']
            ?? null;
    }
}