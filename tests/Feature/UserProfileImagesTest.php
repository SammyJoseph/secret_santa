<?php

namespace Tests\Feature;

use App\Models\FamilyGroup;
use App\Models\GiftSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileImagesTest extends TestCase
{
    use RefreshDatabase;

    private function createFamilyGroup(string $slug = 'familia2026'): FamilyGroup
    {
        return FamilyGroup::create([
            'slug' => $slug,
            'name' => 'Familia ' . $slug,
            'year' => (int) date('Y'),
            'is_active' => true,
            'profile_edit_end_date' => now()->addDays(5),
        ]);
    }

    public function test_profile_displays_fallback_images_when_database_has_no_images(): void
    {
        $familyGroup = $this->createFamilyGroup('fam_empty');

        $user = User::factory()->create([
            'family_group_id' => $familyGroup->id,
            'profile_photo_path' => null,
        ]);
        $user->familyGroups()->attach($familyGroup->id);

        $response = $this->actingAs($user)->get(route('user.profile'));

        $response->assertStatus(200);
        // Profile avatar fallback
        $response->assertSee('assets/images/profile.jpg');
        // Gift suggestion fallbacks
        $response->assertSee('assets/images/no-image.jpg');
        // Temp image inputs should not exist
        $response->assertDontSee('temp-image-filename');
        $response->assertDontSee('temp-gift-image');
    }

    public function test_profile_displays_database_images_when_they_exist(): void
    {
        Storage::fake('public');

        $familyGroup = $this->createFamilyGroup('fam_exist');

        $user = User::factory()->create([
            'family_group_id' => $familyGroup->id,
            'profile_photo_path' => 'profile-photos/user_test.jpg',
        ]);
        $user->familyGroups()->attach($familyGroup->id);

        GiftSuggestion::create([
            'user_id' => $user->id,
            'family_group_id' => $familyGroup->id,
            'suggestion' => 'Libro de ciencia ficción',
            'reference_image_path' => 'gift-suggestions/gift_test.jpg',
        ]);

        $response = $this->actingAs($user)->get(route('user.profile'));

        $response->assertStatus(200);
        $response->assertSee('storage/profile-photos/user_test.jpg');
        $response->assertSee('storage/gift-suggestions/gift_test.jpg');
    }

    public function test_validation_error_preserves_database_state_and_does_not_persist_temp_images(): void
    {
        Storage::fake('public');

        $familyGroup = $this->createFamilyGroup('fam_val');

        $user = User::factory()->create([
            'name' => 'Original Name',
            'family_group_id' => $familyGroup->id,
            'profile_photo_path' => null,
        ]);
        $user->familyGroups()->attach($familyGroup->id);

        $file = UploadedFile::fake()->image('avatar.jpg');

        // Submit with invalid name (less than 3 chars)
        $response = $this->actingAs($user)->put(route('user.update', $user), [
            'name' => 'AB', // invalid
            'profile_photo_path' => $file,
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertNull($user->fresh()->profile_photo_path);

        // When viewing profile again, it still shows fallback
        $profileResponse = $this->actingAs($user)->get(route('user.profile'));
        $profileResponse->assertSee('assets/images/profile.jpg');
        $profileResponse->assertDontSee('temp-image');
    }

    public function test_user_can_update_profile_photo_and_gift_suggestion_images(): void
    {
        Storage::fake('public');

        $familyGroup = $this->createFamilyGroup('fam_upd');

        $user = User::factory()->create([
            'name' => 'Carlos Pérez',
            'family_group_id' => $familyGroup->id,
            'profile_photo_path' => null,
        ]);
        $user->familyGroups()->attach($familyGroup->id);

        $avatarFile = UploadedFile::fake()->image('my_avatar.png', 400, 400);
        $giftFile = UploadedFile::fake()->image('my_gift.jpg', 400, 400);

        $response = $this->actingAs($user)->put(route('user.update', $user), [
            'name' => 'Carlos Pérez Actualizado',
            'profile_photo_path' => $avatarFile,
            'gift_suggestions' => [
                0 => 'Zapatillas deportivas',
                1 => '',
                2 => '',
            ],
            'reference_image_path_0' => $giftFile,
        ]);

        $response->assertRedirect(route('user.profile'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);

        $suggestion = GiftSuggestion::where('user_id', $user->id)
            ->where('family_group_id', $familyGroup->id)
            ->first();

        $this->assertNotNull($suggestion);
        $this->assertEquals('Zapatillas deportivas', $suggestion->suggestion);
        $this->assertNotNull($suggestion->reference_image_path);
        Storage::disk('public')->assertExists($suggestion->reference_image_path);
    }

    public function test_user_can_remove_gift_suggestion_image(): void
    {
        Storage::fake('public');

        $familyGroup = $this->createFamilyGroup('fam_rem');

        $user = User::factory()->create([
            'name' => 'Carlos Pérez',
            'family_group_id' => $familyGroup->id,
            'profile_photo_path' => null,
        ]);
        $user->familyGroups()->attach($familyGroup->id);

        $fakeImagePath = 'gift-suggestions/' . $user->id . '_0_test.jpg';
        Storage::disk('public')->put($fakeImagePath, 'fake image content');

        GiftSuggestion::create([
            'user_id' => $user->id,
            'family_group_id' => $familyGroup->id,
            'suggestion' => 'Zapatillas deportivas',
            'reference_image_path' => $fakeImagePath,
        ]);

        $response = $this->actingAs($user)->put(route('user.update', $user), [
            'name' => 'Carlos Pérez',
            'gift_suggestions' => [
                0 => 'Zapatillas deportivas',
                1 => '',
                2 => '',
            ],
            'remove_reference_image_0' => 1,
        ]);

        $response->assertRedirect(route('user.profile'));
        $response->assertSessionHas('success');

        $suggestion = GiftSuggestion::where('user_id', $user->id)
            ->where('family_group_id', $familyGroup->id)
            ->first();

        $this->assertNotNull($suggestion);
        $this->assertEquals('Zapatillas deportivas', $suggestion->suggestion);
        $this->assertNull($suggestion->reference_image_path);
        Storage::disk('public')->assertMissing($fakeImagePath);
    }
}
