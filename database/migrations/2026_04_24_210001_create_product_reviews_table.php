<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('vote', ['like', 'dislike']);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'user_id']); // one review per user per product
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
