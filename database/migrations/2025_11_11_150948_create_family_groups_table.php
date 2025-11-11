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
        Schema::create('family_groups', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('enable_draw_at')->nullable();
            $table->dateTime('reveal_date')->nullable();
            $table->dateTime('profile_edit_end_date')->nullable();
            $table->timestamps();
        });

        // Crear familia default con las fechas actuales del .env
        DB::table('family_groups')->insert([
            'slug' => 'default',
            'name' => 'Paz',
            'description' => 'Familia original del sistema',
            'is_active' => true,
            'enable_draw_at' => env('SECRET_SANTA_ENABLE_DRAW'),
            'reveal_date' => env('SECRET_SANTA_REVEAL_DATE'),
            'profile_edit_end_date' => env('SECRET_SANTA_PROFILE_EDIT_END_DATE'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_groups');
    }
};
