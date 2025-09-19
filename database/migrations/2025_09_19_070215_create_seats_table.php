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
        
Schema::create('seats', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('match_id'); // foreign key
    $table->string('seat_number');
    $table->boolean('is_taken')->default(false);
    $table->boolean('is_fake')->default(false);
    $table->timestamps();

    $table->foreign('match_id')
          ->references('id')
          ->on('volleyball_matches')
          ->onDelete('cascade');
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
