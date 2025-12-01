<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use App\Models\User;
use Tests\TestCase;

class AlbumControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_all_albums()
    {
        Album::factory()->create(['name' => 'The Life of a Showgirl']);
        Album::factory()->create(['name' => 'Midnights']);

        $response = $this->getJson('/api/albums');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'The Life of a Showgirl'])
            ->assertJsonFragment(['name' => 'Midnights']);
    }

    public function test_index_song_returns_all_album_songs()
    {
        $album = Album::factory()->create([
            'name' => 'Midnights',
        ]);

        $songs = Song::factory()->count(2)->create([
            'album_id' => $album->id,
        ]);

        $response = $this->getJson("/api/album/{$album->id}/songs");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'album' => 'Midnights',
        ]);

        foreach ($songs as $song) {
            $response->assertJsonFragment([
                'id' => $song->id,
                'name' => $song->name,
                'lyrics' => $song->lyrics,
                'songwriter' => $song->songwriter,
                'album_id' => $album->id
            ]);
        }
    }

    public function test_index_song_returns_404_for_missing_album()
    {
		$response = $this->getJson("/api/album/999/songs");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_index_filters_by_needle()
    {
        Album::factory()->create(['name' => 'The Life of a Showgirl']);
        Album::factory()->create(['name' => 'Midnights']);
        Album::factory()->create(['name' => 'GNX']);


        $response = $this->getJson('/api/albums?needle=bar');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Midnights'])
            ->assertJsonFragment(['name' => 'The Life of a Showgirl'])
            ->assertJsonMissing(['name' => 'Born Pink']);
    }

    public function test_store_creates_new_album()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/album', [
            'name' => 'Bob',
            'year' => '2000',
            'genre' => 'POP',
            'artist_id' => $artist->id
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Bob']);

        $this->assertDatabaseHas('albums',
        [
            'name' => 'Bob',
            'year' => '2000',
            'genre' => 'POP',
            'artist_id' => $artist->id
        ]);
    }

    public function test_store_song_creates_new_song_for_album()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $album = Album::factory()->create([
            'name' => 'Midnights',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/album/{$album->id}/song", [
            'name' => 'asd',
            'songwriter' => 'asd',
            'lyrics' => 'asd',
            'album_id' => $album->id
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'asd']);

        $this->assertDatabaseHas('songs',
        [
            'name' => 'asd',
            'songwriter' => 'asd',
            'lyrics' => 'asd',
            'album_id' => $album->id
        ]);
    }

    public function test_store_song_returns_404_for_missing_album()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/album/999/song', [
            'name' => 'szia'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);

    }

    public function test_update_modifies_existing_album()
    {
        $album = Album::factory()->create(['name' => 'Midnights']);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/album/{$album->id}", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Asd']);

        $this->assertDatabaseHas('albums', ['id' => $album->id, 'name' => 'Asd']);
    }

    public function test_update_returns_404_for_missing_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/album/999', [
            'name' => 'Bob Marley'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_update_song_modifies_existing_song_of_album()
    {
        $album = Album::factory()->create(['name' => 'Midnights']);

        $song = Song::factory()->create([
            'album_id' => $album->id,
            'name' => 'Original Name',
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/album/{$album->id}/song/{$song->id}", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Asd']);

        $this->assertDatabaseHas('songs', [
            'id' => $song->id,
            'album_id' => $album->id,
            'name' => 'Asd',
        ]);
    }

    public function test_update_song_returns_404_for_missing_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/album/999/song/1', [
            'name' => 'Bob Marley'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_update_song_returns_404_for_missing_song()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $album = Album::factory()->create([
            'name' => 'Midnights',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/album/{$album->id}/song/9999", [
            'name' => 'Bob Marley'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Song not found']);
    }

    public function test_delete_removes_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $album = Album::factory()->create(['name' => 'Midnights']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/album/{$album->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Album deleted successfully']);

        $this->assertDatabaseMissing('albums', ['id' => $album->id]);
    }

    public function test_delete_returns_404_for_missing_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/album/9999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);

    }

    public function test_delete_song_removes_song_from_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $album = Album::factory()->create(['name' => 'Midnights']);

        $song = Song::factory()->create([
            'album_id' => $album->id,
            'name' => 'Original Name',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/album/{$album->id}/song/{$song->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Song deleted successfully']);

        $this->assertDatabaseMissing('songs', ['id' => $song->id]);
    }

    public function test_delete_song_returns_404_for_missing_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/album/9999/song/1");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_delete_song_returns_404_for_missing_song()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $album = Album::factory()->create(['name' => 'Midnights']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/album/{$album->id}/song/99999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Song not found']);
    }

}
