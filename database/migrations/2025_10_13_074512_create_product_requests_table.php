<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('request_type', ['create_product', 'update_product', 'delete_request', 'price_change'])->default('create_product');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('currency')->default('eur');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('image_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_requests');
    }
};
