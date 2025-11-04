<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Album;
use App\Models\Song;
use App\Models\User;
use Tests\TestCase;

class SongControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_all_songs()
    {
        Song::factory()->create(['name' => 'Nikes']);
        Song::factory()->create(['name' => 'Ivy']);

        $response = $this->getJson('/api/songs');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nikes'])
            ->assertJsonFragment(['name' => 'Ivy']);
    }

    public function test_index_filters_by_needle()
    {
        Song::factory()->create(['name' => 'Nikes']);
        Song::factory()->create(['name' => 'Ivy']);
        Song::factory()->create(['name' => 'Solo']);


        $response = $this->getJson('/api/songs?needle=bar');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Ivy'])
            ->assertJsonFragment(['name' => 'Nikes'])
            ->assertJsonMissing(['name' => 'Godspeed']);
    }
  
    public function test_store_creates_new_song()
    {
        // Létrehozunk egy felhasználót
		$user = User::factory()->create();
		// Lekérjük a tokent
        $token = $user->createToken('TestToken')->plainTextToken;
        $album = Album::factory()->create();

		// A Header-ben elküldjük a tokent és meghívjuk a végpontot (postJson) a szükséges adatokkal
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/song', [
            'name' => 'Bob',
            'songwriter' => 'asd',
            'album_id' => $album->id
        ]);

		// teszteljük, hogy 200-as kódot kapunk-e és a válaszban benne van-e az újonnan hozzáadott adat.
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Bob']);
		
		// teszteljük, hogy az adatbázisban is ott van-e at adat
        $this->assertDatabaseHas('songs', 
        [
            'name' => 'Bob',
            'songwriter' => 'asd',
            'album_id' => $album->id
        ]);
    }

    public function test_update_modifies_existing_song()
    {
        $song = Song::factory()->create(['name' => 'Nikes']);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/song/{$song->id}", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Asd']);

        $this->assertDatabaseHas('songs', ['id' => $song->id, 'name' => 'Asd']);
    }
    
    public function test_update_returns_404_for_missing_song()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/song/999', [
            'name' => 'Bob Marley'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Not found!']);
    }

    public function test_delete_removes_song()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $song = Song::factory()->create(['name' => 'Nikes']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/song/{$song->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Song deleted successfully']);

        $this->assertDatabaseMissing('songs', ['id' => $song->id]);
    } 

}
