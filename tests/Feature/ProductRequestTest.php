<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class); // Nodrošina, ka datubāze tiek atiestatīta pirms katra testa

it('allows a logged-in user to create a product request', function () {
    
    Storage::fake('public'); // Izmanto neīstu “public” disku failu testēšanai

    $user = User::factory()->create(); // Izveido testlietotāju
    $this->actingAs($user); // Pieslēdz testu zem šī lietotāja (simulē pieteikšanos)

    // Izveido testdatus produkta pieprasījumam
    $formData = [
        'title' => 'Test Product',
        'description' => 'This is a test product request.',
        'price' => 100.50,
        'stock' => 10,
        'seller_full_name' => 'John Doe',
        'currency' => 'eur',
        'request_type' => 'create_product',
        'image' => UploadedFile::fake()->image('product.jpg'), // Izmanto viltotu attēlu
    ];
    
    // Nosūta POST pieprasījumu uz produktu pieprasījuma maršrutu
    $response = $this->post(route('product_requests.store'), $formData);

    // Pārbauda, vai pēc izveides tiek veikta pāradresācija uz pieprasījumu sarakstu
    $response->assertRedirect(route('product_requests.index'));

    // Pārbauda, vai ieraksts ar pareizajiem datiem eksistē datubāzē
    $this->assertDatabaseHas('product_requests', [
        'user_id' => $user->id,
        'title' => 'Test Product',
        'description' => 'This is a test product request.',
        'price' => 100.50,
        'currency' => 'eur',
        'request_type' => 'create_product',
    ]);

    // Pārbauda, vai augšupielādētais fails tiešām eksistē "public" diskā
    $productRequest = \App\Models\ProductRequest::first();
    Storage::disk('public')->assertExists($productRequest->image_path);
});
