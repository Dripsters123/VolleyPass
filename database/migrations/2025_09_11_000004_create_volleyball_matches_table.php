<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volleyball_matches', function (Blueprint $table) {
            $table->id();

            $table->boolean('is_local')->default(false);

            $table->string('home_team_name');
            $table->string('away_team_name');

            
            $table->string('home_coach');
            $table->string('away_coach');

            
            $table->string('location');

      
            $table->json('judges');

         
            $table->integer('players_per_team')->default(6);
            $table->string('match_state')->default('scheduled'); 

          
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('estimated_duration_minutes')->default(90);

            
            $table->integer('home_score')->default(0);
            $table->integer('away_score')->default(0);

           
            $table->json('home_players');
            $table->json('away_players');

           
            $table->string('home_logo')->nullable();
            $table->string('away_logo')->nullable();
            $table->string('home_color')->nullable();
            $table->string('away_color')->nullable();

            
            $table->decimal('ticket_price', 8, 2)->default(10.00);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volleyball_matches');
    }
};
