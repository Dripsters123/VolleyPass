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
        Schema::create('volleyball_match_statistics', function (Blueprint $table) {
    $table->id();
    $table->foreignId('match_id')->constrained('volleyball_matches')->cascadeOnDelete();
    $table->string('type');       // Points won, Service errors, etc.
    $table->string('period')->nullable();  // 1ST, 2ND, ALL, etc.
    $table->string('category')->nullable(); // Attacking, Receiving, etc.
    $table->string('home_team')->nullable(); // value or ratio
    $table->string('away_team')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
