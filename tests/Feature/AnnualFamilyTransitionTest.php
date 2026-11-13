<?php

namespace Tests\Feature;

use App\Models\FamilyGroup;
use App\Models\GiftSuggestion;
use App\Models\SecretSantaAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnualFamilyTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_from_previous_year_auto_joins_new_family_on_registration_link(): void
    {
        // 1. Crear familia 2025 y usuario
        $family2025 = FamilyGroup::create([
            'slug' => 'familia2025',
            'name' => 'Familia 2025',
            'year' => 2025,
            'is_active' => true,
        ]);

        $spouse = User::factory()->create([
            'dni' => '99999999',
            'family_group_id' => $family2025->id,
        ]);

        $user = User::factory()->create([
            'dni' => '12345678',
            'family_group_id' => $family2025->id,
            'family_id' => $spouse->id, // Exclusión de pareja año anterior
        ]);

        $family2025->users()->attach([$spouse->id, $user->id]);

        // Crear 3 sugerencias en 2025
        for ($i = 1; $i <= 3; $i++) {
            GiftSuggestion::create([
                'user_id' => $user->id,
                'family_group_id' => $family2025->id,
                'suggestion' => "Regalo $i de 2025",
            ]);
        }

        // 2. Crear nueva familia 2026
        $family2026 = FamilyGroup::create([
            'slug' => 'yellowclass',
            'name' => 'Yellow Class',
            'year' => 2026,
            'is_active' => true,
            'reveal_date' => now()->addDays(5),
            'profile_edit_end_date' => now()->addDays(5),
        ]);

        // 3. Usuario autenticado abre el enlace de la nueva familia
        $response = $this->actingAs($user)->get('/registro?fam=yellowclass');

        // Debe redirigir a su perfil con mensaje de éxito
        $response->assertRedirect(route('user.profile'));
        $response->assertSessionHas('success');

        // 4. Verificar que el usuario fue reasignado como activa a 2026, pero SIGUE siendo miembro de 2025
        $user->refresh();
        $this->assertEquals($family2026->id, $user->family_group_id);
        $this->assertNull($user->family_id);

        $family2025->refresh();
        $family2026->refresh();
        $this->assertTrue($family2025->users->contains('id', $user->id), 'El usuario no debe salir de la familia 2025');
        $this->assertTrue($family2026->users->contains('id', $user->id), 'El usuario debe pertenecer a la nueva familia 2026');

        // 5. Verificar que las sugerencias de 2025 NO se borraron de la base de datos
        $this->assertEquals(3, GiftSuggestion::where('user_id', $user->id)->where('family_group_id', $family2025->id)->count());

        // 6. Verificar que las sugerencias para 2026 están vacías (perfil limpio)
        $this->assertCount(0, $user->giftSuggestions);

        // 7. Visitar /perfil y verificar que carga la nueva familia y campos limpios
        $profileResponse = $this->actingAs($user)->get('/perfil');
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee('Yellow Class');
        $profileResponse->assertSee('Navidad 2026');
        $profileResponse->assertDontSee('Regalo 1 de 2025');

        // 8. Cambiar al historial de 2025 mediante el selector de familias
        $historyResponse = $this->actingAs($user)->get('/perfil?family_group_id=' . $family2025->id);
        $historyResponse->assertStatus(200);
        $historyResponse->assertSee('Familia 2025');
        $historyResponse->assertSee('Regalo 1 de 2025');
    }

    public function test_updating_suggestions_in_new_family_preserves_past_year_suggestions(): void
    {
        $family2025 = FamilyGroup::create([
            'slug' => 'fam2025',
            'name' => 'Fam 2025',
            'year' => 2025,
            'is_active' => true,
        ]);

        $family2026 = FamilyGroup::create([
            'slug' => 'fam2026',
            'name' => 'Fam 2026',
            'year' => 2026,
            'is_active' => true,
            'profile_edit_end_date' => now()->addDays(10),
        ]);

        $user = User::factory()->create([
            'dni' => '87654321',
            'family_group_id' => $family2026->id,
        ]);

        // Sugerencias históricas en 2025
        GiftSuggestion::create([
            'user_id' => $user->id,
            'family_group_id' => $family2025->id,
            'suggestion' => 'Sugerencia antigua 2025',
        ]);

        // Guardar nuevas sugerencias para 2026
        $response = $this->actingAs($user)->put(route('user.update', $user), [
            'name' => $user->name,
            'gift_suggestions' => [
                'Nuevo regalo 1 para 2026',
                'Nuevo regalo 2 para 2026',
                'Nuevo regalo 3 para 2026',
            ],
        ]);

        $response->assertRedirect(route('user.profile'));

        // Verificar que las de 2026 se guardaron con su family_group_id
        $this->assertEquals(3, GiftSuggestion::where('user_id', $user->id)->where('family_group_id', $family2026->id)->count());

        // Verificar que la de 2025 SIGUE existiendo intacta
        $this->assertEquals(1, GiftSuggestion::where('user_id', $user->id)->where('family_group_id', $family2025->id)->count());
        $this->assertEquals('Sugerencia antigua 2025', GiftSuggestion::where('family_group_id', $family2025->id)->first()->suggestion);
    }

    public function test_secret_santa_revealed_shows_only_current_family_suggestions(): void
    {
        $family2025 = FamilyGroup::create([
            'slug' => 'f2025',
            'name' => 'F 2025',
            'year' => 2025,
            'is_active' => true,
        ]);

        $family2026 = FamilyGroup::create([
            'slug' => 'f2026',
            'name' => 'F 2026',
            'year' => 2026,
            'is_active' => true,
            'reveal_date' => now()->subDay(), // Ya revelado
            'profile_edit_end_date' => now()->addDays(10),
        ]);

        $giver = User::factory()->create([
            'dni' => '11111111',
            'family_group_id' => $family2026->id,
        ]);

        $receiver = User::factory()->create([
            'dni' => '22222222',
            'name' => 'Jhans',
            'family_group_id' => $family2026->id,
        ]);

        // Sugerencias de Jhans en 2025
        GiftSuggestion::create([
            'user_id' => $receiver->id,
            'family_group_id' => $family2025->id,
            'suggestion' => 'Polera 2025 de Jhans',
        ]);

        // Sugerencias de Jhans en 2026
        GiftSuggestion::create([
            'user_id' => $receiver->id,
            'family_group_id' => $family2026->id,
            'suggestion' => 'Mouse 2026 de Jhans',
        ]);

        // Asignación de amigo secreto en 2026
        SecretSantaAssignment::create([
            'giver_id' => $giver->id,
            'receiver_id' => $receiver->id,
            'family_group_id' => $family2026->id,
        ]);

        $response = $this->actingAs($giver)->get('/perfil');
        $response->assertStatus(200);
        $response->assertSee('Mi Amigo Secreto es');
        $response->assertSee('Jhans');
        $response->assertSee('Mouse 2026 de Jhans');
        $response->assertDontSee('Polera 2025 de Jhans'); // NUNCA debe mezclar sugerencias del año pasado
    }

    public function test_user_in_past_year_family_sees_warning_banner_on_profile(): void
    {
        $family2025 = FamilyGroup::create([
            'slug' => 'oldfam',
            'name' => 'Familia Antigua',
            'year' => 2025,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'dni' => '33333333',
            'family_group_id' => $family2025->id,
        ]);

        $response = $this->actingAs($user)->get('/perfil');
        $response->assertStatus(200);
        $response->assertSee('Estás viendo los resultados de tu participación en la Navidad');
        $response->assertSee('2025');
    }

    public function test_cannot_switch_if_current_same_year_family_already_drawn(): void
    {
        $familyA = FamilyGroup::create([
            'slug' => 'fama',
            'name' => 'Familia A',
            'year' => 2026,
            'is_active' => true,
        ]);

        $familyB = FamilyGroup::create([
            'slug' => 'famb',
            'name' => 'Familia B',
            'year' => 2026,
            'is_active' => true,
        ]);

        $user1 = User::factory()->create(['family_group_id' => $familyA->id]);
        $user2 = User::factory()->create(['family_group_id' => $familyA->id]);

        // Sorteo realizado en Familia A
        SecretSantaAssignment::create([
            'giver_id' => $user1->id,
            'receiver_id' => $user2->id,
            'family_group_id' => $familyA->id,
        ]);

        // Usuario 1 intenta abrir el link de Familia B
        $response = $this->actingAs($user1)->get('/registro?fam=famb');
        $response->assertRedirect(route('user.profile'));
        $response->assertSessionHas('error');

        // Usuario sigue en Familia A
        $user1->refresh();
        $this->assertEquals($familyA->id, $user1->family_group_id);
    }

    public function test_registration_screen_shows_family_group_name_based_on_slug(): void
    {
        $family = FamilyGroup::create([
            'slug' => 'perez2026',
            'name' => 'Los Pérez',
            'year' => 2026,
            'is_active' => true,
            'profile_edit_end_date' => now()->addDays(5),
        ]);

        $response = $this->get('/registro?fam=perez2026');

        $response->assertStatus(200);
        $response->assertSee('El Amigo Secreto Familiar de Los Pérez');
        $response->assertSee('Navidad 2026');
        $response->assertSee('Opcional');
        $response->assertDontSee('Mi sugerencia de regalo 1');
    }

    public function test_user_can_register_without_gift_suggestions_and_without_photo(): void
    {
        $family = FamilyGroup::create([
            'slug' => 'yellow2026',
            'name' => 'Yellow Class',
            'year' => 2026,
            'is_active' => true,
            'profile_edit_end_date' => now()->addDays(5),
        ]);

        $response = $this->withSession(['registration_family_group_id' => $family->id])
            ->post(route('user.register'), [
                'name' => 'Carlos Perez',
                'dni' => '88887777',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ]);

        $response->assertRedirect(route('user.profile'));
        $this->assertDatabaseHas('users', [
            'name' => 'Carlos Perez',
            'dni' => '88887777',
            'family_group_id' => $family->id,
            'profile_photo_path' => null,
        ]);
        $this->assertDatabaseCount('gift_suggestions', 0);
    }

    public function test_user_from_past_year_can_migrate_from_dni_to_phone_and_join_new_family(): void
    {
        $pastFamily = FamilyGroup::create([
            'slug' => 'fam2025',
            'name' => 'Familia 2025',
            'year' => 2025,
            'is_active' => true,
        ]);

        $pastUser = User::create([
            'name' => 'Maria Lopez',
            'dni' => '47003307',
            'phone' => null,
            'password' => bcrypt('password123'),
            'family_group_id' => $pastFamily->id,
        ]);
        $pastFamily->users()->attach($pastUser->id);

        $newFamily = FamilyGroup::create([
            'slug' => 'fam2026',
            'name' => 'Familia 2026',
            'year' => 2026,
            'is_active' => true,
        ]);

        // 1. Probar verificación previa por DNI
        $checkResponse = $this->withSession(['registration_family_group_id' => $newFamily->id])
            ->getJson(route('user.check-identifier', '47003307'));

        $checkResponse->assertOk()
            ->assertJson([
                'exists' => true,
                'matched_by' => 'dni',
                'name' => 'Maria Lopez',
                'dni' => '47003307',
                'already_registered_this_year' => false,
            ]);

        // 2. Registrarse vinculando el DNI antiguo y actualizando el celular
        $response = $this->withSession(['registration_family_group_id' => $newFamily->id])
            ->post(route('user.register'), [
                'name' => 'Maria Lopez',
                'phone' => '987654321',
                'migrated_dni' => '47003307',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('user.profile'));

        $pastUser->refresh();
        $this->assertEquals('987654321', $pastUser->phone);
        $this->assertEquals('47003307', $pastUser->dni);
        $this->assertEquals($newFamily->id, $pastUser->family_group_id);
        $this->assertTrue($pastUser->familyGroups->contains('id', $pastFamily->id));
        $this->assertTrue($pastUser->familyGroups->contains('id', $newFamily->id));

        // 3. Iniciar sesión usando el nuevo número de celular
        $loginResponse = $this->post('/login', [
            'dni' => '987654321',
            'password' => 'newpassword123',
        ]);
        $loginResponse->assertRedirect(route('user.profile'));
        $this->assertAuthenticatedAs($pastUser);
    }

    public function test_new_user_can_register_directly_with_9_digit_phone(): void
    {
        $family = FamilyGroup::create([
            'slug' => 'navidad2026',
            'name' => 'Navidad 2026',
            'year' => 2026,
            'is_active' => true,
        ]);

        $response = $this->withSession(['registration_family_group_id' => $family->id])
            ->post(route('user.register'), [
                'name' => 'Pedro Castillo',
                'phone' => '912345678',
                'password' => 'clave123',
                'password_confirmation' => 'clave123',
            ]);

        $response->assertRedirect(route('user.profile'));
        $this->assertDatabaseHas('users', [
            'name' => 'Pedro Castillo',
            'phone' => '912345678',
            'family_group_id' => $family->id,
        ]);

        $newUser = User::where('phone', '912345678')->first();
        $this->assertNotNull($newUser);

        // Login con celular
        $loginResponse = $this->post('/login', [
            'dni' => '912345678',
            'password' => 'clave123',
        ]);
        $loginResponse->assertRedirect(route('user.profile'));
        $this->assertAuthenticatedAs($newUser);
    }
}
