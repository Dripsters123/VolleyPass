<?php

use App\Models\Arena;
use App\Models\User;
use App\Models\VolleyballMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class); // Atiestata datubāzi pirms katra testa

it('returns matches when filtering by existing team name', function () {

    Storage::fake('public'); // Neīsta jeb fake failu sistēma testēšanai

    $user = User::factory()->create();

    $arena = Arena::create([
        'name'      => 'Test Arena',
        'user_id'   => $user->id,
        'layout'    => json_encode([]),
        'elements'  => json_encode([]),
        'width'     => 800,
        'height'    => 600,
        'is_public' => false,
    ]);

    // Izveido mači ar zināmu mājas komandas nosaukumu
    VolleyballMatch::create([
        'home_team_name'   => 'LocalHome',
        'away_team_name'   => 'AwayTeam',
        'home_coach'       => 'Test Coach',
        'away_coach'       => 'Away Coach',
        'location'         => 'Rīga, Latvia',
        'judges'           => json_encode([]),
        'home_players'     => json_encode([]),
        'away_players'     => json_encode([]),
        'players_per_team' => 2,
        'is_local'         => true,
        'match_state'      => 'scheduled',
        'arena_id'         => $arena->id,
        'ticket_price'     => 5.00,
        'start_time'       => now()->addDay(),
        'end_time'         => now()->addDay()->addHours(2),
    ]);

    // Nosūta GET pieprasījumu ar esošas komandas filtru
    $response = $this->get(route('local.matches.index', ['team_name' => 'LocalHome']));

    $response->assertStatus(200); // Pārbauda, vai atbilde ir veiksmīga

    // Pārbauda, vai lapa rāda attiecīgās komandas nosaukumu
    $response->assertSeeText('LocalHome');
});

it('shows the empty-state message when filtering by a non-existent team', function () {

    Storage::fake('public'); // Viltota failu sistēma testēšanai

    // Ģenerē nejaušu komandas nosaukumu, kas datubāzē neeksistē
    $random = 'DefinitelyNotARealTeam_' . bin2hex(random_bytes(5));

    // Nosūta GET pieprasījumu ar neesošas komandas nosaukumu
    $response = $this->get(route('local.matches.index', ['team_name' => $random]));

    $response->assertStatus(200); // Lapa joprojām jāielādē bez kļūdām

    // Pārbauda, vai tiek parādīts paziņojums par tukšu rezultātu sarakstu
    // Skats rāda "Nav pieejami mači" bez punkta
    $response->assertSeeText('Nav pieejami mači');
});
