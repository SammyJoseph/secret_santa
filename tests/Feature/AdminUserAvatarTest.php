<?php

namespace Tests\Feature;

use App\Models\FamilyGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUserAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_remove_avatar_via_update_and_fallback_to_profile_photo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $family = FamilyGroup::create([
            'slug' => 'navidad2026',
            'name' => 'Navidad 2026',
            'year' => 2026,
            'is_active' => true,
        ]);

        // Guardar foto de perfil subida por el usuario
        $profilePhoto = 'profile-photos/user_profile.jpg';
        Storage::disk('public')->put($profilePhoto, 'fake user profile photo content');

        // Guardar avatar
        $avatarPhoto = 'funny-profile-photos/user_avatar.jpg';
        Storage::disk('public')->put($avatarPhoto, 'fake avatar content');

        $user = User::factory()->create([
            'name' => 'Sammy',
            'family_group_id' => $family->id,
            'profile_photo_path' => $profilePhoto,
            'funny_profile_photo_path' => $avatarPhoto,
            'nickname' => 'Sam',
        ]);

        // Verificar que la vista de edición muestra el botón X de eliminar avatar
        $response = $this->actingAs($admin)->get(route('admin.users.edit', $user));
        $response->assertOk();
        $response->assertSee('id="btn-remove-avatar"', false);
        $response->assertDontSee('id="btn-remove-avatar" @click.stop class="absolute top-0 right-0 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-md cursor-pointer transition flex items-center justify-center border-2 border-white hidden"', false);

        // Enviar petición PUT con remove_funny_profile_photo = 1
        $updateResponse = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'nickname' => 'Sammy Updated',
            'remove_funny_profile_photo' => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.users.index', ['year' => 2026]));

        $user->refresh();
        $this->assertNull($user->funny_profile_photo_path);
        $this->assertEquals('Sammy Updated', $user->nickname);
        $this->assertEquals($profilePhoto, $user->profile_photo_path);

        // El archivo del avatar debe haber sido borrado de storage
        Storage::disk('public')->assertMissing($avatarPhoto);
        // La foto de perfil original del usuario debe seguir existiendo intacta
        Storage::disk('public')->assertExists($profilePhoto);

        // Ahora al ver el listado de usuarios, debe mostrar la foto de perfil del usuario
        $indexResponse = $this->actingAs($admin)->get(route('admin.users.index', ['year' => 2026]));
        $indexResponse->assertOk();
        $indexResponse->assertSee(Storage::url($profilePhoto), false);
    }

    public function test_admin_can_delete_avatar_via_delete_endpoint(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $avatarPhoto = 'funny-profile-photos/avatar_to_delete.jpg';
        Storage::disk('public')->put($avatarPhoto, 'avatar content');

        $user = User::factory()->create([
            'funny_profile_photo_path' => $avatarPhoto,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.users.delete-avatar', $user));
        $response->assertRedirect();

        $user->refresh();
        $this->assertNull($user->funny_profile_photo_path);
        Storage::disk('public')->assertMissing($avatarPhoto);
    }

    public function test_avatar_button_is_hidden_when_user_has_no_avatar(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $user = User::factory()->create([
            'name' => 'Carlos',
            'funny_profile_photo_path' => null,
            'profile_photo_path' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $user));
        $response->assertOk();
        $response->assertSee('id="btn-remove-avatar"', false);
        $response->assertSee('hidden', false);
    }

    public function test_avatar_takes_priority_over_profile_photo_when_both_exist(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $family = FamilyGroup::create([
            'slug' => 'yellow2026',
            'name' => 'Yellow Class',
            'year' => 2026,
            'is_active' => true,
        ]);

        $profilePhoto = 'profile-photos/user.jpg';
        $avatarPhoto = 'funny-profile-photos/avatar.jpg';
        Storage::disk('public')->put($profilePhoto, 'profile');
        Storage::disk('public')->put($avatarPhoto, 'avatar');

        $user = User::factory()->create([
            'family_group_id' => $family->id,
            'profile_photo_path' => $profilePhoto,
            'funny_profile_photo_path' => $avatarPhoto,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['year' => 2026]));
        $response->assertOk();
        // Debe contener la url del avatar y no la foto de perfil regular en la tabla
        $response->assertSee(Storage::url($avatarPhoto), false);
    }
}
