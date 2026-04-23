<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_requests', function (Blueprint $table) {
            // Make optional fields properly nullable
            $table->string('home_coach')->nullable()->change();
            $table->string('away_coach')->nullable()->change();
            $table->string('location')->nullable()->change();

            // Add arena/layout fields if they don't already exist
            if (!Schema::hasColumn('match_requests', 'arena_name')) {
                $table->string('arena_name')->nullable()->after('location');
            }
            if (!Schema::hasColumn('match_requests', 'arena_layout')) {
                $table->json('arena_layout')->nullable()->after('arena_name');
            }
            if (!Schema::hasColumn('match_requests', 'arena_elements')) {
                $table->json('arena_elements')->nullable()->after('arena_layout');
            }
            if (!Schema::hasColumn('match_requests', 'arena_width')) {
                $table->integer('arena_width')->nullable()->after('arena_elements');
            }
            if (!Schema::hasColumn('match_requests', 'arena_height')) {
                $table->integer('arena_height')->nullable()->after('arena_width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('match_requests', function (Blueprint $table) {
            $table->string('home_coach')->nullable(false)->change();
            $table->string('away_coach')->nullable(false)->change();
            $table->string('location')->nullable(false)->change();

            foreach (['arena_name', 'arena_layout', 'arena_elements', 'arena_width', 'arena_height'] as $col) {
                if (Schema::hasColumn('match_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
