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
        Schema::create('gift_suggestion_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_group_id')->nullable()->constrained('family_groups')->nullOnDelete();
            $table->json('suggestions');
            $table->timestamp('suggested_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'family_group_id', 'created_at'], 'gsh_user_family_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_suggestion_histories');
    }
};
