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
            $table->foreignId('arena_id')->nullable()->constrained('arenas')->onDelete('set null');
            $table->index('arena_id');
        });
    }

    public function down(): void
    {
        Schema::table('volleyball_matches', function (Blueprint $table) {
            $table->dropForeign(['arena_id']);
            $table->dropColumn('arena_id');
        });
    }
};
