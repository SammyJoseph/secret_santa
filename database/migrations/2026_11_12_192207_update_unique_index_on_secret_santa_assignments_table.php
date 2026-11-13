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
        Schema::table('secret_santa_assignments', function (Blueprint $table) {
            $table->dropForeign(['giver_id']);
            $table->dropUnique('secret_santa_assignments_giver_id_receiver_id_unique');
            $table->unique(['family_group_id', 'giver_id']);
            $table->foreign('giver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('secret_santa_assignments', function (Blueprint $table) {
            $table->dropForeign(['giver_id']);
            $table->dropUnique(['family_group_id', 'giver_id']);
            $table->unique(['giver_id', 'receiver_id']);
            $table->foreign('giver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
