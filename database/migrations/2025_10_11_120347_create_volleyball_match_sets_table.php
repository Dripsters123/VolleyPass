<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volleyball_match_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('volleyball_matches')->onDelete('cascade');
            $table->unsignedTinyInteger('set_number'); // 1, 2, 3, etc.
            $table->unsignedTinyInteger('home_score')->nullable(); // 0–99 safe range
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volleyball_match_sets');
    }
};
