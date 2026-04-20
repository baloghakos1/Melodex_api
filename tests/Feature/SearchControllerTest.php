<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_results_grouped_by_type()
    {
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
        ]);

        $album = Album::factory()->create([
            'name' => 'Midnights',
            'genre' => 'Pop',
        ]);

        $song = Song::factory()->create([
            'name' => 'Lavender Haze',
        ]);

        $response = $this->getJson('/api/search?query=Mid');

        $response->assertStatus(200);

        // Album should match
        $response->assertJsonFragment([
            'name' => 'Midnights',
        ]);

        // Ensure grouped structure exists
        $response->assertJsonStructure([
            'albums',
        ]);
    }

    public function test_search_finds_artist_by_name()
    {
        Artist::factory()->create([
            'name' => 'Drake',
        ]);

        $response = $this->getJson('/api/search?query=Drake');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Drake',
            ]);
    }

    public function test_search_finds_album_by_name_and_genre()
    {
        Album::factory()->create([
            'name' => 'Random Album',
            'genre' => 'Rock',
        ]);

        // Search by genre
        $response = $this->getJson('/api/search?query=Rock');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'genre' => 'Rock',
            ]);
    }

    public function test_search_finds_song_by_name()
    {
        Song::factory()->create([
            'name' => 'Shape of You',
        ]);

        $response = $this->getJson('/api/search?query=Shape');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Shape of You',
            ]);
    }

    public function test_search_returns_empty_when_no_results_found()
    {
        $response = $this->getJson('/api/search?query=NoMatch123');

        $response->assertStatus(200)
            ->assertExactJson([]);
    }

    public function test_search_handles_partial_matches()
    {
        Artist::factory()->create([
            'name' => 'The Weeknd',
        ]);

        $response = $this->getJson('/api/search?query=Week');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'The Weeknd',
            ]);
    }
}