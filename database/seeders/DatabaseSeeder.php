<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
   
    public function run(): void
    {
        $admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'VolleyPass',
            'name' => 'Admin VolleyPass',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => 'password'
        ]);

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
