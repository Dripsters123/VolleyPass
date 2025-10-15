<?php

use App\Models\User;



it('Allows the user to register', function () {
    $page = visit('/register');

User::factory()->create([
    'email' => 'kristers@example.com',
    'password' => bcrypt('password'),
]);

    $page->fill('name', 'kristers')
         ->fill('email', 'kristers@example.com')
         ->fill('password', 'password')
         ->fill('password_confirmation', 'password')
         ->click('Reģistrēties');
});
