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

            
            $table->string('home_coach')->nullable();
            $table->string('away_coach')->nullable();

            
            $table->string('location')->nullable();
            $table->json('arena')->nullable();

      
            $table->json('judges')->nullable();

         
            $table->integer('players_per_team')->default(6);
            $table->string('status_type')->default('scheduled'); 
            $table->string('status')->default('scheduled');      
            $table->string('match_state')->default('scheduled'); 

          
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();
            $table->integer('estimated_duration_minutes')->default(90);

            
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();

           
            $table->json('home_players')->nullable();
            $table->json('away_players')->nullable();

           
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
