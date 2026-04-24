<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->string('contact')->nullable()->after('category');
            $table->string('address')->nullable()->after('contact');
            $table->unsignedTinyInteger('delivery_days')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn(['contact', 'address', 'delivery_days']);
        });
    }
};
