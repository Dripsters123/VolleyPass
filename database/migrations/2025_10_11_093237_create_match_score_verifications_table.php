<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_score_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('volleyball_matches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->boolean('approved')->default(false); 
            $table->integer('approvals')->default(0);   
            $table->json('confirmations')->nullable();  
            $table->string('status')->default('pending'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_score_verifications');
    }
};
