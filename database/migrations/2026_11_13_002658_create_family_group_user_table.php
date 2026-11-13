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
        Schema::create('family_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained('family_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['family_group_id', 'user_id']);
            $table->timestamps();
        });

        // 1. Poblar membresías existentes desde la tabla users
        $userMemberships = DB::table('users')
            ->whereNotNull('family_group_id')
            ->select('family_group_id', 'id as user_id')
            ->get()
            ->map(fn($row) => [
                'family_group_id' => $row->family_group_id,
                'user_id' => $row->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->toArray();

        if (!empty($userMemberships)) {
            DB::table('family_group_user')->insertOrIgnore($userMemberships);
        }

        // 2. Poblar también desde secret_santa_assignments para asegurar que todo participante conserve su membresía
        $giverMemberships = DB::table('secret_santa_assignments')
            ->whereNotNull('family_group_id')
            ->whereNotNull('giver_id')
            ->select('family_group_id', 'giver_id as user_id')
            ->distinct()
            ->get()
            ->map(fn($row) => [
                'family_group_id' => $row->family_group_id,
                'user_id' => $row->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->toArray();

        if (!empty($giverMemberships)) {
            DB::table('family_group_user')->insertOrIgnore($giverMemberships);
        }

        $receiverMemberships = DB::table('secret_santa_assignments')
            ->whereNotNull('family_group_id')
            ->whereNotNull('receiver_id')
            ->select('family_group_id', 'receiver_id as user_id')
            ->distinct()
            ->get()
            ->map(fn($row) => [
                'family_group_id' => $row->family_group_id,
                'user_id' => $row->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->toArray();

        if (!empty($receiverMemberships)) {
            DB::table('family_group_user')->insertOrIgnore($receiverMemberships);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_group_user');
    }
};
