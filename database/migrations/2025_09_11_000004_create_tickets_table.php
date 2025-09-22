<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ticket_type')->default('seat');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('amount_paid', 8, 2)->default(0);
            $table->string('currency', 10)->default('EUR');
            $table->string('status')->default('paid');
            $table->string('seat_number')->nullable();
            $table->string('stripe_email')->nullable();
            $table->string('stripe_payment_intent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
