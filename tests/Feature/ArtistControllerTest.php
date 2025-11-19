<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Artist;
use App\Models\User;
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
        // Létrehozunk egy felhasználót
		$user = User::factory()->create();
		// Lekérjük a tokent
        $token = $user->createToken('TestToken')->plainTextToken;

		// A Header-ben elküldjük a tokent és meghívjuk a végpontot (postJson) a szükséges adatokkal
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/artist', [
            'name' => 'Bob',
            'nationality' => 'Hungarian',
            'description' => 'asd',
            'is_band' => 'no'
        ]);

		// teszteljük, hogy 200-as kódot kapunk-e és a válaszban benne van-e az újonnan hozzáadott adat.
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Bob']);
		
		// teszteljük, hogy az adatbázisban is ott van-e at adat
        $this->assertDatabaseHas('artists', 
        [
            'name' => 'Bob',
            'nationality' => 'Hungarian',
            'description' => 'asd',
            'is_band' => 'no'
        ]);
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
}
