<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => 'password'
        ]);

        // Give admin a starting wallet balance
        Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            ['coins' => 500]
        );

        $this->call([
            ArenaSeeder::class,
            LocalMatchSeeder::class,
            ProductsSeeder::class,
            TeamSeeder::class,
        ]);
    }
}
