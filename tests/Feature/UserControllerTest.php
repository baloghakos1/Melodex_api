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
}