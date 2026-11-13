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
        Schema::table('gift_suggestions', function (Blueprint $table) {
            $table->foreignId('family_group_id')->nullable()->after('user_id')->constrained('family_groups')->nullOnDelete();
        });

        // Asignar a las sugerencias existentes la familia del usuario o la familia default (id 1)
        DB::table('gift_suggestions')
            ->whereNull('family_group_id')
            ->update([
                'family_group_id' => DB::raw('(SELECT family_group_id FROM users WHERE users.id = gift_suggestions.user_id)')
            ]);

        DB::table('gift_suggestions')
            ->whereNull('family_group_id')
            ->update(['family_group_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_suggestions', function (Blueprint $table) {
            $table->dropForeign(['family_group_id']);
            $table->dropColumn('family_group_id');
        });
    }
};
