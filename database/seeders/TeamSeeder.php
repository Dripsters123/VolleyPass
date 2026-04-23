<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', '!=', 'admin@example.com')->first();
        if (!$user) return;

        Team::create([
            'user_id' => $user->id,
            'name' => 'Rīgas Vilki',
            'coach' => 'Andris Bērziņš',
            'players_per_team' => 6,
            'players' => [
                ['first_name' => 'Jānis', 'last_name' => 'Kalniņš'],
                ['first_name' => 'Māris', 'last_name' => 'Ozols'],
                ['first_name' => 'Edgars', 'last_name' => 'Liepiņš'],
                ['first_name' => 'Toms', 'last_name' => 'Zariņš'],
                ['first_name' => 'Reinis', 'last_name' => 'Āboltiņš'],
                ['first_name' => 'Kristaps', 'last_name' => 'Vītoliņš'],
            ],
        ]);

        Team::create([
            'user_id' => $user->id,
            'name' => 'Jelgavas Lauvas',
            'coach' => 'Pēteris Siliņš',
            'players_per_team' => 4,
            'players' => [
                ['first_name' => 'Ralfs', 'last_name' => 'Eglītis'],
                ['first_name' => 'Artūrs', 'last_name' => 'Skujiņš'],
                ['first_name' => 'Lauris', 'last_name' => 'Puķīte'],
                ['first_name' => 'Gints', 'last_name' => 'Rudzītis'],
            ],
        ]);

        Team::create([
            'user_id' => $user->id,
            'name' => 'Cēsu Ērgļi',
            'coach' => null,
            'players_per_team' => 2,
            'players' => [
                ['first_name' => 'Valts', 'last_name' => 'Krastiņš'],
                ['first_name' => 'Rolands', 'last_name' => 'Ozoliņš'],
            ],
        ]);
    }
}
