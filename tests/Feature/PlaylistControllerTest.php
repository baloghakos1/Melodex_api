<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Song;
use App\Models\User;
use App\Models\Playlist;
use Tests\TestCase;

class PlaylistControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_all_playlists()
    {
        Playlist::factory()->create(['name' => 'Playlist1']);
        Playlist::factory()->create(['name' => 'Playlist2']);

        $response = $this->getJson('/api/playlists');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Playlist1'])
            ->assertJsonFragment(['name' => 'Playlist2']);
    }

    public function test_single_index_returns_single_playlist()
    {
        $playlist = Playlist::factory()->create([
            'name' => 'Playlist1',
        ]);

        $response = $this->getJson("/api/playlist/{$playlist->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $playlist->id,
                'name' => 'Playlist1',
            ]);
    }

    public function test_single_index_returns_404_for_missing_playlist()
    {
        $response = $this->getJson('/api/playlist/9999');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Playlist not found']);
    }

    public function test_index_song_returns_all_playlist_songs()
    {
        $playlist = Playlist::factory()->create(['name' => 'Playlist1']);

        $songs = Song::factory()->count(2)->create();

        $playlist->songs()->attach($songs->pluck('id'));

        $response = $this->getJson("/api/playlist/{$playlist->id}/songs");

        $response->assertStatus(200)
            ->assertJsonFragment(['playlist' => 'Playlist1']);

        foreach ($songs as $song) {
            $response->assertJsonFragment([
                'id' => $song->id,
                'name' => $song->name,
                'lyrics' => $song->lyrics,
                'songwriter' => $song->songwriter,
            ]);

            $this->assertDatabaseHas('songs_playlists', [
                'playlist_id' => $playlist->id,
                'song_id' => $song->id,
            ]);
        }
    }

    public function test_index_song_returns_404_for_missing_playlist()
    {
        $response = $this->getJson('/api/playlist/9999/songs');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Playlist not found']);
    }

    public function test_store_creates_new_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/playlist', [
            'name' => 'asd',
            'user_id' => $user->id
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'asd']);

        $this->assertDatabaseHas('playlists',
        [
            'name' => 'asd',
            'user_id' => $user->id
        ]);
    }

    public function test_store_returns_409_for_already_existing_playlist_for_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/playlist', [
            'name' => 'asd',
            'user_id' => $user->id
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/playlist', [
            'name' => 'asd',
            'user_id' => $user->id
        ]);

        $response2->assertStatus(409)
            ->assertJsonFragment(['message' => 'Playlist with this name already exists for this user']);
    }

    public function test_store_song_attaches_song_to_playlist()
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->create([
            'user_id' => $user->id
        ]);
        $song = Song::factory()->create();

        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/playlist/{$playlist->id}/song", [
            'song_id' => $song->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Song added successfully'
            ]);

        $this->assertDatabaseHas('songs_playlists', [
            'playlist_id' => $playlist->id,
            'song_id' => $song->id,
        ]);
    }

    public function test_store_song_returns_409_for_already_existing_song_in_playlist() {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->create([
            'user_id' => $user->id
        ]);
        $song = Song::factory()->create();

        $token = $user->createToken('TestToken')->plainTextToken;

        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/playlist/{$playlist->id}/song", [
            'song_id' => $song->id,
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/playlist/{$playlist->id}/song", [
            'song_id' => $song->id,
        ]);

        $response2->assertStatus(409)
            ->assertJsonFragment(['message' => 'Song already exists in playlist']);
    }
}