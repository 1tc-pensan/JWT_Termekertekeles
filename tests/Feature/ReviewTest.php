<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Products;
use App\Models\Reviews;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Értékelések listázása autentikációval
     */
    public function test_authenticated_user_can_list_reviews(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        Reviews::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    /**
     * Test: Értékelések listázása token nélkül (401)
     */
    public function test_unauthenticated_user_cannot_list_reviews(): void
    {
        Reviews::factory()->count(3)->create();

        $response = $this->getJson('/api/reviews');

        $response->assertStatus(401);
    }

    /**
     * Test: Autentikált user létrehozhat értékelést
     */
    public function test_authenticated_user_can_create_review(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $product = Products::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/reviews', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => 'Excellent product!',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'rating' => 5,
                'comment' => 'Excellent product!',
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
        ]);
    }

    /**
     * Test: Token nélkül NEM lehet értékelést létrehozni (401)
     */
    public function test_unauthenticated_user_cannot_create_review(): void
    {
        $user = User::factory()->create();
        $product = Products::factory()->create();

        $response = $this->postJson('/api/reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great!',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test: Értékelés frissítése
     */
    public function test_user_can_update_review(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $review = Reviews::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/reviews/{$review->id}", [
                'rating' => 4,
                'comment' => 'Updated review',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'rating' => 4,
                'comment' => 'Updated review',
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => 'Updated review',
        ]);
    }

    /**
     * Test: Értékelés törlése (soft delete)
     */
    public function test_user_can_delete_review(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $review = Reviews::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('reviews', [
            'id' => $review->id,
        ]);
    }

    /**
     * Test: Termékhez tartozó értékelések lekérése
     */
    public function test_authenticated_user_can_get_product_reviews(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $product = Products::factory()->create();
        Reviews::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/products/{$product->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test: Rating validáció (1-5 között)
     */
    public function test_review_rating_must_be_between_1_and_5(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $product = Products::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/reviews', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'rating' => 6, // Érvénytelen
                'comment' => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    /**
     * Test: Rating kötelező mező
     */
    public function test_review_requires_rating(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $product = Products::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/reviews', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'comment' => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }
}
