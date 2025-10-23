<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('match_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->enum('request_type', ['create_match', 'score_update'])->default('create_match');

            $table->string('home_team');
            $table->string('away_team');

         
            $table->string('home_coach');
            $table->string('away_coach');
            $table->string('location');
            $table->string('home_logo')->nullable();
            $table->string('away_logo')->nullable();

            
            $table->dateTime('start_time');
            $table->dateTime('end_time');

           
            $table->integer('players_per_team');
            $table->json('home_players');
            $table->json('away_players');

          
            $table->json('judges');

           
            $table->integer('score_home')->nullable();
            $table->integer('score_away')->nullable();

          
            $table->foreignId('match_id')->nullable()->constrained('volleyball_matches')->nullOnDelete();

            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_requests');
    }
};
