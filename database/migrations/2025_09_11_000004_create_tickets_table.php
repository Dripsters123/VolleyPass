<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('payment_intent_id')->nullable();
    $table->string('ticket_type');
    $table->foreignId('event_id')->constrained()->onDelete('cascade');
    $table->integer('quantity');
    $table->decimal('amount_paid', 8, 2);
    $table->string('currency', 10)->default('USD');
    $table->string('status')->default('paid');
    $table->string('seat_number')->nullable();
    $table->timestamps();
});

}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
