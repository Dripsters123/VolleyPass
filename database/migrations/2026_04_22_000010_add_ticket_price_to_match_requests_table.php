<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('match_requests', 'ticket_price')) {
                $table->decimal('ticket_price', 8, 2)->nullable()->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('match_requests', function (Blueprint $table) {
            $table->dropColumn('ticket_price');
        });
    }
};
