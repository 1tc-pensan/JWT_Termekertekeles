<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Products;
use App\Models\Reviews;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Admin lekérheti az összes felhasználót
     */
    public function test_admin_can_list_all_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);

        User::factory()->count(10)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'is_admin'],
                ],
                'current_page',
                'per_page',
            ]);
    }

    /**
     * Test: Normál user NEM férhet hozzá admin/users végponthoz
     */
    public function test_regular_user_cannot_access_admin_users(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized. Admin access required.']);
    }

    /**
     * Test: Admin létrehozhat új felhasználót
     */
    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/users', [
                'name' => 'New Admin User',
                'email' => 'newadmin@example.com',
                'password' => 'password123',
                'is_admin' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'New Admin User',
                'email' => 'newadmin@example.com',
                'is_admin' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'is_admin' => true,
        ]);
    }

    /**
     * Test: Admin módosíthat felhasználót
     */
    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/admin/users/{$user->id}", [
                'name' => 'Updated Name',
                'email' => $user->email,
                'is_admin' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Name',
                'is_admin' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'is_admin' => true,
        ]);
    }

    /**
     * Test: Admin törölhet felhasználót
     */
    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/admin/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test: Admin lekérheti az összes terméket értékelésekkel
     */
    public function test_admin_can_list_products_with_reviews(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);

        $product = Products::factory()->create();
        Reviews::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'reviews'],
                ],
            ]);
    }

    /**
     * Test: Admin lekérheti az összes értékelést
     */
    public function test_admin_can_list_all_reviews(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);

        Reviews::factory()->count(15)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reviews');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'rating', 'comment', 'user', 'product'],
                ],
                'current_page',
                'per_page',
            ]);
    }

    /**
     * Test: Admin módosíthat bármely értékelést (moderálás)
     */
    public function test_admin_can_moderate_any_review(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);
        $review = Reviews::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/admin/reviews/{$review->id}", [
                'rating' => 3,
                'comment' => 'Moderated content',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'rating' => 3,
                'comment' => 'Moderated content',
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => 'Moderated content',
        ]);
    }

    /**
     * Test: Admin törölhet bármely értékelést
     */
    public function test_admin_can_delete_any_review(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = auth('api')->login($admin);
        $review = Reviews::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/admin/reviews/{$review->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    /**
     * Test: Token nélkül NEM érhető el admin végpont
     */
    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }
}
