<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\MatchRequest;

uses(RefreshDatabase::class); // Katrs tests tiek palaists ar tīru datubāzi (iztīra tabulas pirms katra testa)

it('allows a logged-in user to create a match request', function () {
    $faker = Faker::create(); // Izveido Faker instanci datu ģenerēšanai

    // Izveido lietotāju datubāzē ar Faker datiem
    $user = User::create([
        'name' => $faker->name(),
        'email' => $faker->unique()->safeEmail(),
        'password' => bcrypt('Password123!'),
    ]);

    $this->actingAs($user); // Pieslēdz testu zem šī lietotāja (simulē autentificētu lietotāju)

    // Ģenerē divus mājas komandas spēlētājus ar vārdiem/uzvārdiem
    $homePlayers = [
        ['first_name' => $faker->regexify('[A-Za-z]{5,10}'), 'last_name' => $faker->regexify('[A-Za-z]{5,10}')],
        ['first_name' => $faker->regexify('[A-Za-z]{5,10}'), 'last_name' => $faker->regexify('[A-Za-z]{5,10}')]
    ];

    // Ģenerē divus viesu komandas spēlētājus ar vārdiem/uzvārdiem
    $awayPlayers = [
        ['first_name' => $faker->regexify('[A-Za-z]{5,10}'), 'last_name' => $faker->regexify('[A-Za-z]{5,10}')],
        ['first_name' => $faker->regexify('[A-Za-z]{5,10}'), 'last_name' => $faker->regexify('[A-Za-z]{5,10}')]
    ];

    // Izveido testēšanas formu ar visiem vajadzīgajiem laukiem
    $formData = [
        'home_team' => $faker->regexify('[A-Za-z ]{5,15}'), // Mājas komandas nosaukums
        'away_team' => $faker->regexify('[A-Za-z ]{5,15}'), // Viesu komandas nosaukums
        'home_coach' => $faker->regexify('[A-Za-z ]{5,12}'), // Mājas trenera vārds
        'away_coach' => $faker->regexify('[A-Za-z ]{5,12}'), // Viesu trenera vārds
        'judges' => implode(', ', [$faker->regexify('[A-Za-z ]{5,12}'), $faker->regexify('[A-Za-z ]{5,12}')]), // Tiesnešu saraksts
        'location' => $faker->city() . ', Latvia', // Norises vieta
        'players_per_team' => 2, // Spēlētāju skaits komandā
        'start_time' => now()->addDay()->format('Y-m-d\TH:i'), // Spēles sākuma laiks
        'end_time' => now()->addDay()->addHour()->format('Y-m-d\TH:i'), // Spēles beigu laiks
        'home_players' => $homePlayers, // Mājas spēlētāju masīvs
        'away_players' => $awayPlayers, // Viesu spēlētāju masīvs
    ];

    // Izsūta POST pieprasījumu uz maršrutu, kas atbild par mača pieprasījuma saglabāšanu
    $response = $this->post(route('match_requests.store'), $formData);

    // Pārbauda, vai lietotājs tiek pāradresēts uz savu maču pieprasījumu lapu pēc veiksmīgas izveides
    $response->assertRedirect(route('match_requests.my'));

    // Pārbauda, vai datubāzē ir izveidots jauns ieraksts ar pareiziem laukiem
    $this->assertDatabaseHas('match_requests', [
        'user_id' => $user->id,
        'home_team' => $formData['home_team'],
        'away_team' => $formData['away_team'],
        'home_coach' => $formData['home_coach'],
        'away_coach' => $formData['away_coach'],
        'location' => $formData['location'],
        'players_per_team' => $formData['players_per_team'],
        'status' => 'pending', // Pārbauda, vai statuss pēc izveides ir "pending"
    ]);
});
