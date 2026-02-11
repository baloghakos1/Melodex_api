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

    public function getTrackStreamUrl(string $trackId): ?string
    {
        $host = $this->getHost();
        return "{$host}/v1/tracks/{$trackId}/stream";
    }

    public function getImageUrl(?array $artwork, string $size = '1000x1000'): ?string
    {
        if (!$artwork || !isset($artwork['1000x1000'])) {
            return null;
        }
        
        $cid = $artwork['1000x1000'];
        $host = $this->getHost();
        return "{$host}/v1/image_upload/{$cid}";
    }

    public function getProfilePictureUrl(?array $profilePicture, string $size = '1000x1000'): ?string
    {
        if (!$profilePicture || !isset($profilePicture['1000x1000'])) {
            return null;
        }
        
        $cid = $profilePicture['1000x1000'];
        $host = $this->getHost();
        return "{$host}/v1/image_upload/{$cid}";
    }
}