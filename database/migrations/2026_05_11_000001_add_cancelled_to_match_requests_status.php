<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE match_requests MODIFY COLUMN status ENUM('pending','reviewing','accepted','rejected','appealed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE match_requests MODIFY COLUMN status ENUM('pending','reviewing','accepted','rejected','appealed') NOT NULL DEFAULT 'pending'");
    }
};
