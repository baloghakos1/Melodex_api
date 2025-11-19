<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Member;
use App\Models\Artist;
use App\Models\User;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_all_members()
    {
        Member::factory()->create(['name' => 'Alex Turner']);
        Member::factory()->create(['name' => 'Jamie Cook']);

        $response = $this->getJson('/api/members');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Alex Turner'])
            ->assertJsonFragment(['name' => 'Jamie Cook']);
    }

    public function test_index_filters_by_needle()
    {
        Member::factory()->create(['name' => 'Alex Turner']);
        Member::factory()->create(['name' => 'Jamie Cook']);
        Member::factory()->create(['name' => 'Phil Selway']);


        $response = $this->getJson('/api/members?needle=bar');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Jamie Cook'])
            ->assertJsonFragment(['name' => 'Alex Turner'])
            ->assertJsonMissing(['name' => 'Lisa']);
    }
  
    public function test_store_creates_new_member()
    {
        // Létrehozunk egy felhasználót
		$user = User::factory()->create();
		// Lekérjük a tokent
        $token = $user->createToken('TestToken')->plainTextToken;
        $artist = Artist::factory()->create();

		// A Header-ben elküldjük a tokent és meghívjuk a végpontot (postJson) a szükséges adatokkal
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/member', [
            'name' => 'Bob',
            'instrument' => 'Piano',
            'year' => '2000',
            'artist_id' => $artist->id
        ]);

		// teszteljük, hogy 200-as kódot kapunk-e és a válaszban benne van-e az újonnan hozzáadott adat.
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Bob']);
		
		// teszteljük, hogy az adatbázisban is ott van-e at adat
        $this->assertDatabaseHas('members', 
        [
            'name' => 'Bob',
            'instrument' => 'Piano',
            'year' => '2000',
            'artist_id' => $artist->id
        ]);
    }

    public function test_update_modifies_existing_member()
    {
        $member = Member::factory()->create(['name' => 'Alex Turner']);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/member/{$member->id}", [
            'name' => 'Asd'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Asd']);

        $this->assertDatabaseHas('members', ['id' => $member->id, 'name' => 'Asd']);
    }
    
    public function test_update_returns_404_for_missing_member()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/member/999', [
            'name' => 'Bob Marley'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Member not found']);
    }

    public function test_delete_removes_member()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $member = Member::factory()->create(['name' => 'Alex Turner']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/member/{$member->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Member deleted successfully']);

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
    } 

}
