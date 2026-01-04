<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Products;
use App\Models\Reviews;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user létrehozása
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'password' => bcrypt('admin123'),
        ]);

        // 10 user létrehozása
        $users = User::factory(10)->create();

        // 20 termék létrehozása
        $products = Products::factory(20)->create();

        // 50 értékelés létrehozása (random userek és termékek)
        Reviews::factory(50)->create([
            'user_id' => fn() => $users->random()->id,
            'product_id' => fn() => $products->random()->id,
        ]);

        // Teszt user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
