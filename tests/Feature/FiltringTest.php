<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('returns matches when filtering by existing team name', function () {
   
    Storage::fake('public');

    $this->seed(\Database\Seeders\LocalMatchSeeder::class);

    $response = $this->get(route('local.matches.index', ['team_name' => 'LocalHome']));

    $response->assertStatus(200);

   
    $response->assertSeeText('LocalHome');
    $response->assertSeeText('LocalHome 2v2');
});

it('shows the empty-state message when filtering by a non-existent team', function () {
    Storage::fake('public');

    $this->seed(\Database\Seeders\LocalMatchSeeder::class);

    $random = 'DefinitelyNotARealTeam_' . bin2hex(random_bytes(5));

    $response = $this->get(route('local.matches.index', ['team_name' => $random]));

    $response->assertStatus(200);

    $response->assertSeeText('Nav pieejami mači.');
});
