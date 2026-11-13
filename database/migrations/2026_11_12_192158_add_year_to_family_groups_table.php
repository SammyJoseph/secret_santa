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
        Schema::table('family_groups', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->default((int) date('Y'))->after('description');
        });

        // Set year based on created_at for all existing families
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::table('family_groups')->update([
                'year' => DB::raw("COALESCE(cast(strftime('%Y', created_at) as integer), 2025)")
            ]);
        } else {
            DB::table('family_groups')->update([
                'year' => DB::raw('COALESCE(YEAR(created_at), 2025)')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_groups', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};
