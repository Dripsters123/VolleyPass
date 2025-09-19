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
        $table->json('tournament')->nullable()->after('arena');
        $table->json('season')->nullable()->after('tournament');
        $table->json('round')->nullable()->after('season');
        $table->json('league')->nullable()->after('round');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('volleyball_matches', function (Blueprint $table) {
        $table->dropColumn(['tournament', 'season', 'round', 'league']);
    });
    }
};
