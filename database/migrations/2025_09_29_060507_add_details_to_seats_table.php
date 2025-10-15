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
        Schema::table('seats', function (Blueprint $table) {
            if (! Schema::hasColumn('seats', 'side')) {
                $table->string('side')->nullable()->after('seat_number');
            }
            if (! Schema::hasColumn('seats', 'row')) {
                $table->integer('row')->nullable()->after('side');
            }
            if (! Schema::hasColumn('seats', 'number')) {
                $table->integer('number')->nullable()->after('row');
            }
            if (! Schema::hasColumn('seats', 'price')) {
                $table->decimal('price', 8, 2)->default(10.00)->after('number');
            }
            if (! Schema::hasColumn('seats', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('is_taken');
            }
           
            $table->unique(['match_id', 'seat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            if (Schema::hasColumn('seats', 'side')) $table->dropColumn('side');
            if (Schema::hasColumn('seats', 'row')) $table->dropColumn('row');
            if (Schema::hasColumn('seats', 'number')) $table->dropColumn('number');
            if (Schema::hasColumn('seats', 'price')) $table->dropColumn('price');
            if (Schema::hasColumn('seats', 'user_id')) $table->dropColumn('user_id');
            $table->dropUnique(['match_id','seat_number']);
        });

    }
};
