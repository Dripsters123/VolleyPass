<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change status enum to include 'reviewing' and 'appealed'
        DB::statement("ALTER TABLE match_requests MODIFY COLUMN status ENUM('pending','reviewing','accepted','rejected','appealed') NOT NULL DEFAULT 'pending'");

        Schema::table('match_requests', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->text('appeal_message')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('match_requests', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'appeal_message']);
        });

        DB::statement("ALTER TABLE match_requests MODIFY COLUMN status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending'");
    }
};
