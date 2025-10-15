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
         Schema::table('volleyball_matches', function (Blueprint $table) {
            if (! Schema::hasColumn('volleyball_matches', 'ticket_price')) {
                $table->decimal('ticket_price', 8, 2)->default(10.00)->after('end_time');
            }
            if (! Schema::hasColumn('volleyball_matches', 'is_local')) {
                $table->boolean('is_local')->default(false)->after('ticket_price')->comment('true for admin-created local matches');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volleyball_matches', function (Blueprint $table) {
            if (Schema::hasColumn('volleyball_matches', 'ticket_price')) {
                $table->dropColumn('ticket_price');
            }
            if (Schema::hasColumn('volleyball_matches', 'is_local')) {
                $table->dropColumn('is_local');
            }
        });
    }
};
