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
         Schema::create('volleyball_matches', function (Blueprint $table) {
            $table->id();
            $table->string('home_team_name');
            $table->string('away_team_name');
            $table->string('status_type');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->json('arena')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volleyball_matches');
    }
};
