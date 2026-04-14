<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use App\Models\Song;
use App\Models\User;
use App\Models\Playlist;
use Tests\TestCase;

class UserControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_login_user_can_login()
    {
        $password = 'password123';

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson('/api/user/login', [
            'email' => 'test@example.com',
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'email',
                    'token'
                ]
            ]);

        $this->assertNotNull($response->json('user.token'));
    }

    public function test_login_fails_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/user/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid email or password',
            ]);
    }

    public function test_index_returns_all_users() {
        $user = User::factory()->create(['email' => 'admin1@admin.com']);
        User::factory()->create(['email' => 'admin2@admin.com']);
        User::factory()->create(['email' => 'admin3@admin.com']);
        $token = $user->createToken('TestToken')->plainTextToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => 'admin1@admin.com'])
            ->assertJsonFragment(['email' => 'admin2@admin.com'])
            ->assertJsonFragment(['email' => 'admin3@admin.com']);
    }

    public function test_index_playlist_returns_all_playlists_for_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $user2 = User::factory()->create();
        Playlist::factory()->create([
            'name' => 'Playlist1',
            'user_id' => $user->id
        ]);
        Playlist::factory()->create([
            'name' => 'Playlist2',
            'user_id' => $user->id
        ]);
        Playlist::factory()->create([
            'name' => 'Playlist3',
            'user_id' => $user2->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/{$user->id}/playlists");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Playlist1'])
            ->assertJsonFragment(['name' => 'Playlist2']);
    }

    public function test_index_playlist_returns_404_for_missing_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/9999/playlists");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'User not found']);
    }

    public function test_single_index_playlist_returns_single_playlist_for_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist = Playlist::factory()->create([
            'name' => 'Playlist1',
            'user_id' => $user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/{$user->id}/playlist/{$playlist->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Playlist1']);
    }

    public function test_single_index_playlist_returns_404_for_missing_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist = Playlist::factory()->create([
            'name' => 'Playlist1',
            'user_id' => $user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/9999/playlist/{$playlist->id}");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'User not found']);
    }

    public function test_single_index_playlist_returns_404_for_missing_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist = Playlist::factory()->create([
            'name' => 'Playlist1',
            'user_id' => $user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/{$user->id}/playlist/9999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Playlist not found']);
    }

    public function test_index_playlist_song_returns_all_songs_in_playlist_for_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist = Playlist::factory()->create([
            'name' => 'Playlist1',
            'user_id' => $user->id
        ]);
        $songs = Song::factory()->count(2)->create();
        $playlist->songs()->attach($songs->pluck('id'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/{$user->id}/playlist/{$playlist->id}/songs");

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

    public function test_index_playlist_song_returns_404_for_missing_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist = Playlist::factory()->create([
            'name' => 'Playlist1',
            'user_id' => $user->id
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/9999/playlist/{$playlist->id}/songs");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'User not found']);
    }

    public function test_index_playlist_song_returns_404_for_missing_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist = Playlist::factory()->create([
            'name' => 'Playlist1',
            'user_id' => $user->id
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/{$user->id}/playlist/9999/songs");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Playlist not found']);
    }

    public function test_store_playlist_creates_new_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/playlist", [
            'name' => 'asd',
            'user_id' => "$user->id"
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'asd']);

        $this->assertDatabaseHas('playlists',
        [
            'name' => 'asd',
            'user_id' => "$user->id"
        ]);
    }

    public function test_store_playlist_returns_404_for_missing_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/9999/playlist", [
            'name' => 'asd',
            'user_id' => "$user->id"
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'User not found']);
    }

    public function test_store_playlist_returns_409_for_already_existing_playlist_for_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/playlist", [
            'name' => 'asd',
            'user_id' => $user->id
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/playlist", [
            'name' => 'asd',
            'user_id' => $user->id
        ]);

        $response2->assertStatus(409)
            ->assertJsonFragment(['message' => 'Playlist with this name already exists']);
    }

    public function test_store_playlist_song_attaches_song_to_playlist() {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->create([
            'user_id' => $user->id
        ]);
        $song = Song::factory()->create();

        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/playlist/{$playlist->id}/song", [
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

    public function test_store_playlist_song_returns_409_for_already_existing_song_in_playlist() {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->create([
            'user_id' => $user->id
        ]);
        $song = Song::factory()->create();

        $token = $user->createToken('TestToken')->plainTextToken;

        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/playlist/{$playlist->id}/song", [
            'song_id' => $song->id,
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/playlist/{$playlist->id}/song", [
            'song_id' => $song->id,
        ]);

        $response2->assertStatus(409)
            ->assertJsonFragment(['message' => 'Song already exists in playlist']);
    }

    public function test_store_playlist_song_returns_404_for_missing_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/9999/playlist/1/song");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'User not found']);
    }

    public function test_store_playlist_song_returns_404_for_missing_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/playlist/9999/song");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Playlist not found']);
    }

    public function test_store_song_playlists_syncs_playlists_successfully()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $song = Song::factory()->create();

        $playlist1 = Playlist::factory()->create(['user_id' => $user->id]);
        $playlist2 = Playlist::factory()->create(['user_id' => $user->id]);

        $song->playlists()->attach($playlist1->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/song/{$song->id}/playlists", [
            'playlists' => [$playlist2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Playlists updated successfully'
            ]);

        $this->assertDatabaseHas('songs_playlists', [
            'song_id' => $song->id,
            'playlist_id' => $playlist2->id,
        ]);

        $this->assertDatabaseMissing('songs_playlists', [
            'song_id' => $song->id,
            'playlist_id' => $playlist1->id,
        ]);
    }

    public function test_store_song_playlists_returns_404_for_missing_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $song = Song::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/9999/song/{$song->id}/playlists", [
            'playlists' => [],
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'message' => 'User not found'
            ]);
    }

    public function test_store_song_playlists_ignores_invalid_playlists()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $song = Song::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/song/{$song->id}/playlists", [
            'playlists' => [999999],
        ]);
        
        $response->assertStatus(422);

        $this->assertDatabaseMissing('songs_playlists', [
            'song_id' => $song->id,
        ]);
    }

    public function test_song_playlists_returns_playlists_containing_song()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $song = Song::factory()->create();

        $playlist1 = Playlist::factory()->create(['user_id' => $user->id]);
        $playlist2 = Playlist::factory()->create(['user_id' => $user->id]);

        $playlist1->songs()->attach($song->id);
        $playlist2->songs()->attach($song->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/{$user->id}/song/{$song->id}/playlists");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'song_id' => (string) $song->id,
            ])
            ->assertJsonFragment([
                'id' => $playlist1->id,
            ])
            ->assertJsonFragment([
                'id' => $playlist2->id,
            ]);
    }

    public function test_song_playlists_returns_404_if_user_not_found()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $song = Song::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/user/9999/song/{$song->id}/playlists");

        $response->assertStatus(404)
            ->assertJsonFragment([
                'message' => 'User not found'
            ]);
    }



    public function test_update_playlist_modifies_existing_playlist() {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->create([
            'user_id' => $user->id,
            'name' => 'szia'
        ]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/user/{$user->id}/playlist/{$playlist->id}", [
            'name' => 'asd',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Playlist updated successfully',
                'name' => 'asd'
            ]);

        $this->assertDatabaseHas('playlists', [
            'name' => 'asd',
            'user_id' => $user->id,
        ]);
    }

    public function test_update_playlist_returns_404_for_missing_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/user/9999/playlist/1');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'User not found']);
    }

    public function test_update_playlist_returns_404_for_missing_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/user/{$user->id}/playlist/9999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Playlist not found']);
    }

    public function test_update_playlist_returns_409_for_already_existing_playlist_for_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist1 = Playlist::factory()->create([
            'user_id' => $user->id,
            'name' => 'asd1'
        ]);
        $playlist2 = Playlist::factory()->create([
            'user_id' => $user->id,
            'name' => 'asd2'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/user/{$user->id}/playlist/{$playlist2->id}", [
            'name' => 'asd1',
        ]);

        $response->assertStatus(409)
            ->assertJsonFragment(['message' => 'Playlist with this name already exists']);
    }

    public function test_delete_playlist_removes_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $playlist = Playlist::factory()->create([
            'user_id' => $user->id,
            'name' => 'asd1'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/user/{$user->id}/playlist/{$playlist->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Playlist deleted successfully']);

        $this->assertDatabaseMissing('playlists', ['id' => $playlist->id]);
    }

    public function test_delete_playlist_returns_404_for_missing_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/user/9999/playlist/1");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'User not found']);
    }

    public function test_delete_playlist_returns_404_for_missing_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/user/{$user->id}/playlist/9999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Playlist not found']);
    }

    public function test_delete_playlist_song_removes_song_from_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist = Playlist::factory()->create([
            'user_id' => $user->id
        ]);
        $song = Song::factory()->create();
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/user/{$user->id}/playlist/{$playlist->id}/song", [
            'song_id' => $song->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/user/{$user->id}/playlist/{$playlist->id}/song/{$song->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Song removed from playlist']);

        $this->assertDatabaseMissing('songs_playlists', [
            'playlist_id' => $playlist->id,
            'song_id' => $song->id,
        ]);
    }

    public function test_delete_playlist_song_returns_404_for_missing_user() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/user/9999/playlist/1/song/1");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'User not found']);
    }

    public function test_delete_playlist_song_returns_404_for_missing_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/user/{$user->id}/playlist/9999/song/1");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Playlist not found']);
    }

    public function test_delete_playlist_song_returns_404_for_missing_song_in_playlist() {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $playlist = Playlist::factory()->create([
            'user_id' => $user->id
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/user/{$user->id}/playlist/{$playlist->id}/song/9999");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Song not found in playlist']);
    }

}