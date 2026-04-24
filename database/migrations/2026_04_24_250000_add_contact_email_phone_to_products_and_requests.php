<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('contact');
            $table->string('contact_phone')->nullable()->after('contact_email');
        });

        Schema::table('product_requests', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('contact');
            $table->string('contact_phone')->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone']);
        });

        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone']);
        });
    }
};
