<?php

namespace Tests\Feature;

use App\Models\FamilyGroup;
use App\Models\GiftSuggestion;
use App\Models\GiftSuggestionHistory;
use App\Models\SecretSantaAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GiftSuggestionHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createFamilyGroup(array $attributes = []): FamilyGroup
    {
        return FamilyGroup::create(array_merge([
            'slug' => 'fam_' . uniqid(),
            'name' => 'Familia Demo',
            'year' => (int) date('Y'),
            'is_active' => true,
            'profile_edit_end_date' => now()->addDays(5),
        ], $attributes));
    }

    public function test_updating_suggestions_and_images_archives_previous_snapshot_and_keeps_old_image(): void
    {
        Storage::fake('public');

        $familyGroup = $this->createFamilyGroup(['name' => 'Familia García']);
        $user = User::factory()->create([
            'name' => 'Diego Castro',
            'family_group_id' => $familyGroup->id,
        ]);
        $familyGroup->users()->attach($user->id);

        $oldImagePath = 'gift-suggestions/' . $user->id . '_0_oldimage.jpg';
        Storage::disk('public')->put($oldImagePath, 'old-image-bytes');

        $originalTimestamp = Carbon::create(2026, 11, 18, 14, 30, 0);
        Carbon::setTestNow($originalTimestamp);
        GiftSuggestion::create([
            'user_id' => $user->id,
            'family_group_id' => $familyGroup->id,
            'suggestion' => 'Gorra deportiva',
            'reference_image_path' => $oldImagePath,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 11, 19, 9, 15, 0));

        $newImage = UploadedFile::fake()->image('nuevo_regalo.jpg', 400, 400);

        $response = $this->actingAs($user)->put(route('user.update', $user), [
            'name' => 'Diego Castro',
            'gift_suggestions' => [
                0 => 'Lentes de sol polarizados',
                1 => 'Libro de cocina',
                2 => '',
            ],
            'reference_image_path_0' => $newImage,
        ]);

        Carbon::setTestNow();

        $response->assertRedirect(route('user.profile'));

        // La imagen anterior debe permanecer en disco para consulta histórica
        Storage::disk('public')->assertExists($oldImagePath);

        $currentSuggestions = GiftSuggestion::where('user_id', $user->id)
            ->where('family_group_id', $familyGroup->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $currentSuggestions);
        $this->assertSame('Lentes de sol polarizados', $currentSuggestions[0]->suggestion);
        $this->assertNotNull($currentSuggestions[0]->reference_image_path);
        $this->assertNotSame($oldImagePath, $currentSuggestions[0]->reference_image_path);
        Storage::disk('public')->assertExists($currentSuggestions[0]->reference_image_path);

        $history = GiftSuggestionHistory::where('user_id', $user->id)->first();
        $this->assertNotNull($history);
        $this->assertSame($familyGroup->id, $history->family_group_id);
        $this->assertSame('18/11/2026 14:30', $history->suggested_at?->format('d/m/Y H:i'));
        $this->assertSame([
            [
                'suggestion' => 'Gorra deportiva',
                'reference_image_path' => $oldImagePath,
            ],
        ], $history->suggestions);
    }

    public function test_updating_only_name_or_profile_photo_does_not_create_history_and_preserves_timestamps(): void
    {
        Storage::fake('public');

        $familyGroup = $this->createFamilyGroup();
        $user = User::factory()->create([
            'name' => 'María López',
            'family_group_id' => $familyGroup->id,
        ]);
        $familyGroup->users()->attach($user->id);

        $existingImage = 'gift-suggestions/' . $user->id . '_0_keep.jpg';
        Storage::disk('public')->put($existingImage, 'keep-image');

        $createdAt = Carbon::create(2026, 11, 17, 10, 0, 0);
        Carbon::setTestNow($createdAt);
        $originalSuggestion = GiftSuggestion::create([
            'user_id' => $user->id,
            'family_group_id' => $familyGroup->id,
            'suggestion' => 'Perfume floral',
            'reference_image_path' => $existingImage,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 11, 19, 10, 0, 0));

        $newAvatar = UploadedFile::fake()->image('avatar.jpg', 400, 400);

        $response = $this->actingAs($user)->put(route('user.update', $user), [
            'name' => 'María López Actualizada',
            'profile_photo_path' => $newAvatar,
            'gift_suggestions' => [
                0 => 'Perfume floral',
                1 => '',
                2 => '',
            ],
        ]);

        Carbon::setTestNow();

        $response->assertRedirect(route('user.profile'));

        $this->assertDatabaseCount('gift_suggestion_histories', 0);

        $persistedSuggestion = GiftSuggestion::find($originalSuggestion->id);
        $this->assertNotNull($persistedSuggestion);
        $this->assertTrue($persistedSuggestion->updated_at->equalTo($createdAt));
    }

    public function test_clearing_all_suggestions_archives_previous_snapshot(): void
    {
        $familyGroup = $this->createFamilyGroup();
        $user = User::factory()->create([
            'name' => 'Luis Torres',
            'family_group_id' => $familyGroup->id,
        ]);
        $familyGroup->users()->attach($user->id);

        GiftSuggestion::create([
            'user_id' => $user->id,
            'family_group_id' => $familyGroup->id,
            'suggestion' => 'Audífonos bluetooth',
        ]);

        $response = $this->actingAs($user)->put(route('user.update', $user), [
            'name' => 'Luis Torres',
            'gift_suggestions' => [
                0 => '',
                1 => '',
                2 => '',
            ],
        ]);

        $response->assertRedirect(route('user.profile'));

        $this->assertSame(0, GiftSuggestion::where('user_id', $user->id)->count());
        $this->assertDatabaseCount('gift_suggestion_histories', 1);

        $history = GiftSuggestionHistory::where('user_id', $user->id)->first();
        $this->assertSame([
            [
                'suggestion' => 'Audífonos bluetooth',
                'reference_image_path' => null,
            ],
        ], $history->suggestions);
    }

    public function test_adding_suggestions_for_the_first_time_does_not_create_empty_history(): void
    {
        $familyGroup = $this->createFamilyGroup();
        $user = User::factory()->create([
            'name' => 'Ana Ruiz',
            'family_group_id' => $familyGroup->id,
        ]);
        $familyGroup->users()->attach($user->id);

        $response = $this->actingAs($user)->put(route('user.update', $user), [
            'name' => 'Ana Ruiz',
            'gift_suggestions' => [
                0 => 'Taza térmica',
                1 => 'Camisa blanca',
                2 => '',
            ],
        ]);

        $response->assertRedirect(route('user.profile'));

        $this->assertSame(2, GiftSuggestion::where('user_id', $user->id)->count());
        $this->assertDatabaseCount('gift_suggestion_histories', 0);
    }

    public function test_admin_sees_read_only_suggestion_history_in_edit_view(): void
    {
        Storage::fake('public');

        $familyGroup = $this->createFamilyGroup(['name' => 'Familia Navideña']);
        $admin = User::factory()->create([
            'is_admin' => true,
            'family_group_id' => $familyGroup->id,
        ]);
        $user = User::factory()->create([
            'name' => 'Carlos Vega',
            'family_group_id' => $familyGroup->id,
        ]);
        $familyGroup->users()->attach([$admin->id, $user->id]);

        $archivedImage = 'gift-suggestions/' . $user->id . '_0_hist.jpg';
        Storage::disk('public')->put($archivedImage, 'hist-image');

        $writtenAt = Carbon::create(2026, 11, 18, 16, 45, 0);
        $replacedAt = Carbon::create(2026, 11, 19, 8, 20, 0);

        Carbon::setTestNow($replacedAt);
        GiftSuggestionHistory::create([
            'user_id' => $user->id,
            'family_group_id' => $familyGroup->id,
            'suggestions' => [
                [
                    'suggestion' => 'Sugerencia histórica secreta',
                    'reference_image_path' => $archivedImage,
                ],
            ],
            'suggested_at' => $writtenAt,
        ]);
        Carbon::setTestNow();

        GiftSuggestion::create([
            'user_id' => $user->id,
            'family_group_id' => $familyGroup->id,
            'suggestion' => 'Sugerencia actual vigente',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertSee('Ver historial (1)');
        $response->assertSee('Historial de sugerencias');
        $response->assertSee('Sugerencia histórica secreta');
        $response->assertSee('Reemplazada el 19/11/2026 08:20');
        $response->assertSee('Escrita el 18/11/2026 16:45');
        $response->assertSee('storage/' . $archivedImage);
        $response->assertSee('Solo lectura');
    }

    public function test_non_admin_users_cannot_access_admin_edit_or_see_history_on_profile(): void
    {
        $familyGroup = $this->createFamilyGroup([
            'name' => 'Familia Privada',
            'reveal_date' => now()->subDay(),
        ]);

        $receiver = User::factory()->create([
            'name' => 'Mariana Sol',
            'family_group_id' => $familyGroup->id,
        ]);
        $giver = User::factory()->create([
            'name' => 'Pedro Díaz',
            'is_admin' => false,
            'family_group_id' => $familyGroup->id,
        ]);
        $familyGroup->users()->attach([$receiver->id, $giver->id]);

        GiftSuggestion::create([
            'user_id' => $receiver->id,
            'family_group_id' => $familyGroup->id,
            'suggestion' => 'Sugerencia vigente actual',
        ]);

        $assignment = SecretSantaAssignment::create([
            'giver_id' => $giver->id,
            'receiver_id' => $receiver->id,
            'family_group_id' => $familyGroup->id,
        ]);

        // El receptor actualiza sus sugerencias después del sorteo
        $this->actingAs($receiver)->put(route('user.update', $receiver), [
            'name' => 'Mariana Sol',
            'gift_suggestions' => [
                0 => 'Sugerencia actualizada hoy',
                1 => '',
                2 => '',
            ],
        ])->assertRedirect(route('user.profile'));

        // La asignación del sorteo permanece intacta
        $this->assertDatabaseHas('secret_santa_assignments', [
            'id' => $assignment->id,
            'giver_id' => $giver->id,
            'receiver_id' => $receiver->id,
            'family_group_id' => $familyGroup->id,
        ]);

        // Ni el participante ni su amigo secreto pueden acceder a la vista admin
        $this->actingAs($receiver)->get(route('admin.users.edit', $receiver))->assertStatus(403);
        $this->actingAs($giver)->get(route('admin.users.edit', $receiver))->assertStatus(403);

        // Ninguno ve el historial ni el texto histórico en /perfil
        $receiverProfile = $this->actingAs($receiver)->get(route('user.profile'));
        $receiverProfile->assertStatus(200);
        $receiverProfile->assertDontSee('Ver historial');
        $receiverProfile->assertDontSee('Sugerencia vigente actual');

        $giverProfile = $this->actingAs($giver)->get(route('user.profile'));
        $giverProfile->assertStatus(200);
        $giverProfile->assertSee('Sugerencia actualizada hoy');
        $giverProfile->assertDontSee('Ver historial');
        $giverProfile->assertDontSee('Sugerencia vigente actual');
    }
}
