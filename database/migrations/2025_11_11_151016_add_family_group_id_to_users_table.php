<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('family_group_id')->nullable()->after('family_id')->constrained('family_groups')->onDelete('set null');
        });

        // Asignar todos los usuarios existentes a la familia default (id = 1)
        DB::table('users')->whereNull('family_group_id')->update([
            'family_group_id' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['family_group_id']);
            $table->dropColumn('family_group_id');
        });
    }
};
