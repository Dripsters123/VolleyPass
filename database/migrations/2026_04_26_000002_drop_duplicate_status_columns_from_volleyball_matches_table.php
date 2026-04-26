<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('volleyball_matches', function (Blueprint $table) {
            if (Schema::hasColumn('volleyball_matches', 'status_type')) {
                $table->dropColumn('status_type');
            }

            if (Schema::hasColumn('volleyball_matches', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('volleyball_matches', function (Blueprint $table) {
            if (!Schema::hasColumn('volleyball_matches', 'status_type')) {
                $table->string('status_type')->default('scheduled')->after('players_per_team');
            }

            if (!Schema::hasColumn('volleyball_matches', 'status')) {
                $table->string('status')->default('scheduled')->after('status_type');
            }
        });
    }
};
