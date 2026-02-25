<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Artist;
use App\Models\Album;
use App\Models\User;
use App\Models\Song;
use App\Models\Member;
use Tests\TestCase;

class ArtistControllerTest extends TestCase
{

    use RefreshDatabase;
    public function test_index_returns_all_artists()
    {
        Artist::factory()->create(['name' => 'Taylor Swift']);
        Artist::factory()->create(['name' => 'BLACKPINK']);

        $response = $this->getJson('/api/artists');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Taylor Swift'])
            ->assertJsonFragment(['name' => 'BLACKPINK']);
    }

    public function test_single_index_returns_single_artist()
    {
        $artist = Artist::factory()->create([
            'name' => 'BLACKPINK',
        ]);

        $response = $this->getJson("/api/artist/{$artist->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $artist->id,
                'name' => 'BLACKPINK',
            ]);
    }

    public function test_single_index_returns_404_for_missing_artist()
    {
        $response = $this->getJson('/api/artist/9999');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_single_index_album_returns_single_album()
    {
        $artist = Artist::factory()->create([
            'name' => 'BLACKPINK',
        ]);

        $album = Album::factory()->create([
            'artist_id' => $artist->id,
        ]);

        $response = $this->getJson("/api/artist/{$artist->id}/album/{$album->id}");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'artist' => 'BLACKPINK',
        ]);

        $response->assertJsonFragment([
            'id' => $album->id,
            'name' => $album->name,
            'cover' => $album->cover,
            'year' =>$album->year,
            'genre' => $album->genre,
            'artist_id' => $artist->id
        ]);
    }

    public function test_single_index_album_returns_404_for_missing_artist()
    {
        $response = $this->getJson('/api/artist/9999/album/1');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_single_index_album_returns_404_for_missing_album()
    {
        $artist = Artist::factory()->create([
            'name' => 'BLACKPINK',
        ]);
        $response = $this->getJson("/api/artist/{$artist->id}/album/9999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_index_albums_returns_all_artist_albums()
    {
        $artist = Artist::factory()->create(['name' => 'Taylor Swift']);
        $albums = Album::factory()->count(3)->create([
            'artist_id' => $artist->id,
        ]);
        $response = $this->getJson("/api/artist/{$artist->id}/albums");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'artist' => 'Taylor Swift',
        ]);

        foreach ($albums as $album) {
            $response->assertJsonFragment([
                'id' => $album->id,
                'name' => $album->name,
                'cover' => $album->cover,
                'year' =>$album->year,
                'genre' => $album->genre,
                'artist_id' => $artist->id
            ]);
        }
    }

    public function test_index_albums_returns_404_for_missing_artist() {
        $response = $this->getJson("/api/artist/9999/albums");
        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }



    public function test_index_songs_returns_all_artist_album_songs()
    {
        $artist = Artist::factory()->create(['name' => 'Taylor Swift']);
        $album = Album::factory()->create([
            'artist_id' => $artist->id,
            'name' => 'Midnights',
        ]);
        $songs = Song::factory()->count(3)->create([
            'album_id' => $album->id,
        ]);

        $response = $this->getJson("/api/artist/{$artist->id}/album/{$album->id}/songs");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'artist' => 'Taylor Swift',
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

    public function test_index_songs_returns_404_for_missing_artist() {
        $response = $this->getJson("/api/artist/9999/album/1/songs");
        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_index_songs_returns_404_for_missing_album() {
        $artist = Artist::factory()->create(['name' => 'Taylor Swift']);
        $response = $this->getJson("/api/artist/{$artist->id}/album/99999/songs");
        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }


    public function test_index_filters_by_needle()
    {
        Artist::factory()->create(['name' => 'Taylor Swift']);
        Artist::factory()->create(['name' => 'BLACKPINK']);
        Artist::factory()->create(['name' => 'Bad Bunny']);


        $response = $this->getJson('/api/artists?needle=bar');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'BLACKPINK'])
            ->assertJsonFragment(['name' => 'Taylor Swift'])
            ->assertJsonMissing(['name' => 'Billie Eilish']);
    }

    public function test_store_creates_new_artist()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/artist', [
            'name' => 'Bob',
            'nationality' => 'Hungarian',
            'description' => 'asd',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Bob']);

        $this->assertDatabaseHas('artists',
        [
            'name' => 'Bob',
            'nationality' => 'Hungarian',
            'description' => 'asd',
        ]);
    }

    public function test_store_album_creates_new_album()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/artist/{$artist->id}/album", [
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

    public function test_store_album_returns_404_for_missing_artist()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/artist/{9999}/album", [
            'name' => 'Bob',
            'instrument' => 'Vocals',
            'year' => '2000',
            'artist_id' => $artist->id
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_store_song_creates_new_song()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id' => $artist->id
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/artist/{$artist->id}/album/{$album->id}/song", [
            'name' => 'Bob',
            'songwriter' => 'John',
            'album_id' => $album->id
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Song created successfully']);

        $this->assertDatabaseHas('songs',
        [
            'name' => 'Bob',
            'songwriter' => 'John',
            'album_id' => $album->id
        ]);
    }

    public function test_store_song_returns_404_for_missing_artist()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id' => $artist->id
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/artist/{9999}/album/{1}/song", [
            'name' => 'Bob',
            'songwriter' => 'John',
            'album_id' => $album->id
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_store_song_returns_404_for_missing_album()
    {
		$user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id' => $artist->id
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/artist/{$artist->id}/album/{99999}/song", [
            'name' => 'Bob',
            'songwriter' => 'John',
            'album_id' => $album->id
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_update_modifies_existing_artist()
    {
        $artist = Artist::factory()->create(['name' => 'Taylor Swift']);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/artist/{$artist->id}", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Asd']);

        $this->assertDatabaseHas('artists', ['id' => $artist->id, 'name' => 'Asd']);
    }

    public function test_update_returns_404_for_missing_artist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/artist/999', [
            'name' => 'Bob Marley'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }


    public function test_update_album_modifies_existing_album()
    {
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id'=> $artist->id
        ]);
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/artist/{$artist->id}/album/{$album->id}", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Asd']);

        $this->assertDatabaseHas('albums', ['id' => $album->id, 'name' => 'Asd']);
    }

    public function test_update_album_returns_404_for_missing_artist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/artist/999/album/1', [
            'name' => 'Asd'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_update_album_returns_404_for_missing_album()
    {
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/artist/{$artist->id}/album/99999", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_update_song_modifies_existing_song()
    {
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id'=> $artist->id
        ]);
        $song = Song::factory()->create([
            'name' => 'Boom',
            'album_id' => $album->id
        ]);
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/artist/{$artist->id}/album/{$album->id}/song/{$song->id}", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Asd']);

        $this->assertDatabaseHas('songs', ['id' => $song->id, 'name' => 'Asd']);
    }

    public function test_update_song_returns_404_for_missing_artist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/artist/999/album/1/song/1', [
            'name' => 'Asd'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_update_song_returns_404_for_missing_album()
    {
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/artist/{$artist->id}/album/99999/song/1", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_update_song_returns_404_for_missing_song()
    {
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1,
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id'=> $artist->id
        ]);
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/artist/{$artist->id}/album/{$album->id}/song/999999", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Song not found']);
    }

    public function test_delete_removes_artist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create(['name' => 'Taylor Swift']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/{$artist->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Artist deleted successfully']);

        $this->assertDatabaseMissing('artists', ['id' => $artist->id]);
    }

    public function test_delete_returns_404_for_missing_artist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create(['name' => 'Taylor Swift']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/99999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }


    public function test_delete_album_removes_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id' => $artist->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/{$artist->id}/album/{$album->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Album deleted successfully']);

        $this->assertDatabaseMissing('albums', ['id' => $album->id]);
    }

    public function test_delete_album_returns_404_for_missing_artist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create(['name' => 'Taylor Swift']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/99999/album/1");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_delete_album_returns_404_for_missing_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/{$artist->id}/album/99999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_delete_song_removes_song()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id' => $artist->id
        ]);
        $song = Song::factory()->create([
            'name' => 'Asd',
            'album_id' => $album->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/{$artist->id}/album/{$album->id}/song/{$song->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Song deleted successfully']);

        $this->assertDatabaseMissing('songs', ['id' => $song->id]);
    }

    public function test_delete_song_returns_404_for_missing_artist()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create(['name' => 'Taylor Swift']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/99999/album/1/song/1");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Artist not found']);
    }

    public function test_delete_song_returns_404_for_missing_album()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/{$artist->id}/album/99999/song/1");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Album not found']);
    }

    public function test_delete_song_returns_404_for_missing_song()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create([
            'name' => 'Taylor Swift',
            'id' => 1
        ]);
        $album = Album::factory()->create([
            'name' => 'Midnights',
            'artist_id' => $artist->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/artist/{$artist->id}/album/{$album->id}/song/99999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Song not found']);
    }

}
