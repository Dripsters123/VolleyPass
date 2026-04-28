<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('arenas')->whereNull('layout')->update(['layout' => json_encode([])]);
        DB::table('arenas')->whereNull('elements')->update(['elements' => json_encode([])]);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE arenas MODIFY layout JSON NOT NULL');
            DB::statement('ALTER TABLE arenas MODIFY elements JSON NOT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE arenas MODIFY layout JSON NULL');
            DB::statement('ALTER TABLE arenas MODIFY elements JSON NULL');
        }
    }
};
