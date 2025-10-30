<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as Faker;

uses(RefreshDatabase::class);

it('registers a random user successfully', function () {
    Event::fake();

    $faker = Faker::create();

    $name = $faker->firstName();
    $email = $faker->unique()->safeEmail();
    $password = 'Password123!';

    $page = visit('/register');

    $page->fill('input[name=name]', $name)
         ->fill('input[name=email]', $email)
         ->fill('input[name=password]', $password)
         ->fill('input[name=password_confirmation]', $password)
         ->click('button[type=submit]') 
         ->assertSee('Sveiks');       

    $this->assertAuthenticated();

    Event::assertDispatched(Registered::class);
});
