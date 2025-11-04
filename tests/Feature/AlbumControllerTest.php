<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Album;
use App\Models\Artist;
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
        // Létrehozunk egy felhasználót
		$user = User::factory()->create();
		// Lekérjük a tokent
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create();

		// A Header-ben elküldjük a tokent és meghívjuk a végpontot (postJson) a szükséges adatokkal
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/album', [
            'name' => 'Bob',
            'year' => '2000',
            'genre' => 'POP',
            'artist_id' => $artist->id
        ]);

		// teszteljük, hogy 200-as kódot kapunk-e és a válaszban benne van-e az újonnan hozzáadott adat.
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Bob']);
		
		// teszteljük, hogy az adatbázisban is ott van-e at adat
        $this->assertDatabaseHas('albums', 
        [
            'name' => 'Bob',
            'year' => '2000',
            'genre' => 'POP',
            'artist_id' => $artist->id
        ]);
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
            ->assertJsonFragment(['message' => 'Not found!']);
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

}
