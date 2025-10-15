<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('match_id')->constrained('volleyball_matches')->cascadeOnDelete();
        $table->string('prediction'); 
        $table->decimal('staked_coins', 12, 2)->default(0); 
        $table->string('status')->default('pending'); 
        $table->decimal('reward', 12, 2)->nullable(); 
        $table->timestamps();
    });
    }


    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
