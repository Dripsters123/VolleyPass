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

           
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->onDelete('set null');

         
            $table->string('side');
            $table->unsignedSmallInteger('row');
            $table->unsignedSmallInteger('number');
            $table->string('seat_number');
            $table->unique(['match_id', 'seat_number'], 'unique_match_seat');

            $table->decimal('price', 8, 2)->default(10.00);
            $table->foreignId('reserved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reserved_until')->nullable();

           
            $table->timestamps();

            $table->foreign('match_id')
                  ->references('id')
                  ->on('volleyball_matches')
                  ->onDelete('cascade');

            $table->index(['reserved_by', 'reserved_until']);
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
