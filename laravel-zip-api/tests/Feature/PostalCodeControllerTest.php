<?php

namespace Tests\Feature;

use App\Models\County;
use App\Models\Place;
use App\Models\PostalCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostalCodeControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that index returns all postal codes
     */
    public function test_index_returns_all_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Budapest']);
        $place = Place::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);
        PostalCode::factory()->create(['postal_code' => '1011', 'place_id' => $place->id]);
        PostalCode::factory()->create(['postal_code' => '1012', 'place_id' => $place->id]);

        $response = $this->getJson('/api/postal-codes');

        $response->assertStatus(200)
            ->assertJsonFragment(['postal_code' => '1011'])
            ->assertJsonFragment(['postal_code' => '1012']);
    }

    /**
     * Test that show returns a specific postal code
     */
    public function test_show_returns_postal_code()
    {
        $postalCode = PostalCode::factory()->create(['postal_code' => '1013']);

        $response = $this->getJson("/api/postal-codes/{$postalCode->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['postal_code' => '1013']);
    }

    /**
     * Test that show returns 404 for non-existing postal code
     */
    public function test_show_returns_404_for_missing_postal_code()
    {
        $response = $this->getJson('/api/postal-codes/999');

        $response->assertStatus(404);
    }

    /**
     * Test that store creates a new postal code with authentication
     */
    public function test_store_creates_new_postal_code()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/postal-codes', [
            'postal_code' => '1014',
            'place_name' => 'Budapest',
            'county_name' => 'Budapest'
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['postal_code' => '1014']);

        $this->assertDatabaseHas('postal_codes', ['postal_code' => '1014']);
    }

    /**
     * Test that store requires authentication
     */
    public function test_store_requires_authentication()
    {
        $response = $this->postJson('/api/postal-codes', [
            'postal_code' => '1015',
            'place_name' => 'Budapest',
            'county_name' => 'Budapest'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that update modifies existing postal code
     */
    public function test_update_modifies_existing_postal_code()
    {
        $postalCode = PostalCode::factory()->create(['postal_code' => '1016']);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/postal-codes/{$postalCode->id}", [
            'postal_code' => '1017'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['postal_code' => '1017']);

        $this->assertDatabaseHas('postal_codes', ['id' => $postalCode->id, 'postal_code' => '1017']);
    }

    /**
     * Test that update returns 404 for non-existing postal code
     */
    public function test_update_returns_404_for_missing_postal_code()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/postal-codes/999', [
            'postal_code' => '1018'
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test that delete removes postal code
     */
    public function test_delete_removes_postal_code()
    {
        $postalCode = PostalCode::factory()->create(['postal_code' => '1019']);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/postal-codes/{$postalCode->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Deleted successfully']);

        $this->assertDatabaseMissing('postal_codes', ['id' => $postalCode->id]);
    }

    /**
     * Test that delete requires authentication
     */
    public function test_delete_requires_authentication()
    {
        $postalCode = PostalCode::factory()->create();

        $response = $this->deleteJson("/api/postal-codes/{$postalCode->id}");

        $response->assertStatus(401);
    }
}
