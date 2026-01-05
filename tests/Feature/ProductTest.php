<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Products;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Termékek listázása autentikációval
     */
    public function test_authenticated_user_can_list_products(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        Products::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    /**
     * Test: Termékek listázása token nélkül (401)
     */
    public function test_unauthenticated_user_cannot_list_products(): void
    {
        Products::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    /**
     * Test: Egy termék lekérése autentikációval
     */
    public function test_authenticated_user_can_get_single_product(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $product = Products::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
            ]);
    }

    /**
     * Test: Admin létrehozhat új terméket
     */
    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', [
                'name' => 'New Laptop',
                'description' => 'Gaming laptop',
                'price' => 299999,
                'stock' => 10,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'New Laptop',
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Laptop',
        ]);
    }

    /**
     * Test: Normál user NEM hozhat létre terméket (403)
     */
    public function test_regular_user_cannot_create_product(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', [
                'name' => 'New Laptop',
                'description' => 'Gaming laptop',
                'price' => 299999,
                'stock' => 10,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test: Admin módosíthat terméket
     */
    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);

        $product = Products::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Updated Laptop',
                'description' => 'Updated description',
                'price' => 399999,
                'stock' => 20,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Laptop',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Laptop',
        ]);
    }

    /**
     * Test: Admin törölhet terméket (soft delete)
     */
    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);

        $product = Products::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test: Termék validációs hiba (name kötelező)
     */
    public function test_product_creation_requires_name(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', [
                'description' => 'Gaming laptop',
                'price' => 299999,
                'stock' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
