<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('contact')->nullable()->after('stock');       // seller contact (phone/email)
            $table->string('address')->nullable()->after('contact');     // where product is held
            $table->unsignedTinyInteger('delivery_days')->nullable()->after('address'); // est. delivery days
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['contact', 'address', 'delivery_days']);
        });
    }
};
