<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('arenas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('layout')->nullable(); // Store the custom layout configuration
            $table->json('elements')->nullable(); // Store seats, court, etc. positions
            $table->integer('width')->default(800); // Canvas width
            $table->integer('height')->default(600); // Canvas height
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arenas');
    }
};
