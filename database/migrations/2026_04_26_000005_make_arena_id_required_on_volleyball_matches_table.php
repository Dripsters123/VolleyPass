<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $nullArenaMatchCount = DB::table('volleyball_matches')->whereNull('arena_id')->count();

        if ($nullArenaMatchCount > 0) {
            $ownerId = DB::table('users')->where('role', 'admin')->value('id')
                ?? DB::table('users')->value('id');

            if (!$ownerId) {
                throw new RuntimeException('Cannot backfill arena_id: users table is empty while null arena_id rows exist.');
            }

            $fallbackArenaId = DB::table('arenas')->where('name', 'System Backfill Arena')->value('id');
            if (!$fallbackArenaId) {
                $fallbackArenaId = DB::table('arenas')->insertGetId([
                    'name' => 'System Backfill Arena',
                    'description' => 'Auto-created to backfill matches without arena.',
                    'user_id' => $ownerId,
                    'layout' => json_encode([]),
                    'elements' => json_encode([]),
                    'width' => 800,
                    'height' => 600,
                    'is_public' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('volleyball_matches')
                ->whereNull('arena_id')
                ->update(['arena_id' => $fallbackArenaId]);
        }

        // SQLite does not support MODIFY COLUMN — skip constraint change, it stores the value fine
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('volleyball_matches', function (Blueprint $table) {
                $table->dropForeign(['arena_id']);
            });

            DB::statement('ALTER TABLE volleyball_matches MODIFY arena_id BIGINT UNSIGNED NOT NULL');

            Schema::table('volleyball_matches', function (Blueprint $table) {
                $table->foreign('arena_id')->references('id')->on('arenas')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('volleyball_matches', function (Blueprint $table) {
                $table->dropForeign(['arena_id']);
            });

            DB::statement('ALTER TABLE volleyball_matches MODIFY arena_id BIGINT UNSIGNED NULL');

            Schema::table('volleyball_matches', function (Blueprint $table) {
                $table->foreign('arena_id')->references('id')->on('arenas')->nullOnDelete();
            });
        }
    }
};
