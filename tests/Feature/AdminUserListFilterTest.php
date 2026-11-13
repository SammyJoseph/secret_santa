<?php

namespace Tests\Feature;

use App\Models\FamilyGroup;
use App\Models\SecretSantaAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserListFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_list_filters_secret_santa_and_family_by_selected_year_and_family(): void
    {
        config(['app.env' => 'local']);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $family2025 = FamilyGroup::create([
            'slug' => 'paz2025',
            'name' => 'Familia Paz',
            'year' => 2025,
            'is_active' => true,
        ]);

        $family2026 = FamilyGroup::create([
            'slug' => 'yellow2026',
            'name' => 'Yellow Class',
            'year' => 2026,
            'is_active' => true,
        ]);

        $sammy = User::factory()->create([
            'name' => 'Sammy',
            'dni' => '47003307',
            'family_group_id' => $family2026->id,
            'created_at' => '2025-10-18 13:19:11',
        ]);

        $jhans = User::factory()->create([
            'name' => 'Jhans',
            'dni' => '45725932',
            'family_group_id' => $family2025->id,
            'created_at' => '2025-10-18 13:20:00',
        ]);

        // Membresías en pivot family_group_user
        $family2025->users()->attach($sammy->id, [
            'created_at' => '2025-10-18 13:19:11',
            'updated_at' => '2025-10-18 13:19:11',
        ]);
        $family2025->users()->attach($jhans->id, [
            'created_at' => '2025-10-18 13:20:00',
            'updated_at' => '2025-10-18 13:20:00',
        ]);

        $family2026->users()->attach($sammy->id, [
            'created_at' => '2026-11-13 04:21:37',
            'updated_at' => '2026-11-13 04:21:37',
        ]);

        // Asignación de amigo secreto en 2025 (Sammy le regala a Jhans en Familia Paz)
        SecretSantaAssignment::create([
            'giver_id' => $sammy->id,
            'receiver_id' => $jhans->id,
            'family_group_id' => $family2025->id,
        ]);

        // 1. Filtrar por Año 2026
        $response2026 = $this->actingAs($admin)->get(route('admin.users.index', ['year' => 2026]));
        $response2026->assertOk();
        $response2026->assertSee('Yellow Class');
        $response2026->assertSee('Sammy');
        // Para 2026, Sammy NO debe tener asignado a Jhans como amigo secreto
        $response2026->assertDontSee('Jhans');
        $response2026->assertSee('No asignado');
        // El botón de acción debe estar disponible (no decir "No disponible")
        $response2026->assertDontSee('No disponible');

        // 2. Filtrar por Año 2025
        $response2025 = $this->actingAs($admin)->get(route('admin.users.index', ['year' => 2025]));
        $response2025->assertOk();
        $response2025->assertSee('Familia Paz');
        $response2025->assertSee('Sammy');
        // Para 2025, Sammy SÍ tiene asignado a Jhans
        $response2025->assertSee('Jhans');
        // Como el sorteo ya se realizó para 2025, muestra No disponible
        $response2025->assertSee('No disponible');

        // 3. Filtrar específicamente por Familia Paz (ID de familia 2025)
        $responsePaz = $this->actingAs($admin)->get(route('admin.users.index', [
            'year' => 2025,
            'family_group_id' => $family2025->id,
        ]));
        $responsePaz->assertOk();
        $responsePaz->assertSee('Sammy');
        $responsePaz->assertSee('Jhans');

        // 4. Filtrar específicamente por Yellow Class (ID de familia 2026)
        $responseYellow = $this->actingAs($admin)->get(route('admin.users.index', [
            'year' => 2026,
            'family_group_id' => $family2026->id,
        ]));
        $responseYellow->assertOk();
        $responseYellow->assertSee('Sammy');
        $responseYellow->assertDontSee('Jhans');
        $responseYellow->assertSee('No asignado');
    }

    public function test_admin_user_profile_view_respects_family_context(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $family2025 = FamilyGroup::create([
            'slug' => 'paz2025',
            'name' => 'Familia Paz',
            'year' => 2025,
            'is_active' => true,
            'reveal_date' => now()->subDays(5),
            'profile_edit_end_date' => now()->subDays(2),
        ]);

        $family2026 = FamilyGroup::create([
            'slug' => 'yellow2026',
            'name' => 'Yellow Class',
            'year' => 2026,
            'is_active' => true,
            'reveal_date' => now()->addDays(5),
            'profile_edit_end_date' => now()->addDays(2),
        ]);

        $sammy = User::factory()->create([
            'name' => 'Sammy',
            'dni' => '47003307',
            'family_group_id' => $family2026->id,
        ]);

        $jhans = User::factory()->create([
            'name' => 'Jhans',
            'dni' => '45725932',
            'family_group_id' => $family2025->id,
        ]);

        $family2025->users()->attach($sammy->id);
        $family2025->users()->attach($jhans->id);
        $family2026->users()->attach($sammy->id);

        SecretSantaAssignment::create([
            'giver_id' => $sammy->id,
            'receiver_id' => $jhans->id,
            'family_group_id' => $family2025->id,
        ]);

        // Ver perfil con contexto de la familia 2026
        $response2026 = $this->actingAs($admin)->get(route('admin.users.profile', [
            'user' => $sammy->id,
            'family_group_id' => $family2026->id,
        ]));
        $response2026->assertOk();
        // Para 2026 no debe mostrar a Jhans porque no hay asignación en 2026 y no está revelado
        $response2026->assertDontSee('Jhans');
        $response2026->assertSee('Mi Amigo Secreto es...');

        // Ver perfil con contexto de la familia 2025 (Paz)
        $response2025 = $this->actingAs($admin)->get(route('admin.users.profile', [
            'user' => $sammy->id,
            'family_group_id' => $family2025->id,
        ]));
        $response2025->assertOk();
        $response2025->assertSee('2025');
        $response2025->assertSee('Jhans');
    }

    public function test_admin_draw_defaults_to_current_year_and_performs_clean_draw(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Asegurar que la familia default de la migración tenga año 2025 como en producción
        FamilyGroup::where('slug', 'default')->update(['year' => 2025]);

        $family2025 = FamilyGroup::firstOrCreate(
            ['slug' => 'paz2025'],
            ['name' => 'Familia Paz 2025', 'year' => 2025, 'is_active' => true]
        );

        $family2026 = FamilyGroup::create([
            'slug' => 'yellow2026',
            'name' => 'Yellow Class',
            'year' => 2026,
            'is_active' => true,
            'enable_draw_at' => now()->subMinute(),
        ]);

        $user1 = User::factory()->create(['name' => 'Participante 1', 'family_group_id' => $family2026->id]);
        $user2 = User::factory()->create(['name' => 'Participante 2', 'family_group_id' => $family2026->id]);
        $user3 = User::factory()->create(['name' => 'Participante 3', 'family_group_id' => $family2026->id]);
        $pastUser = User::factory()->create(['name' => 'Usuario Pasado Solo 2025', 'family_group_id' => $family2025->id]);

        $family2026->users()->attach([$user1->id, $user2->id, $user3->id]);
        $family2025->users()->attach([$pastUser->id]);

        // Simular sorteo realizado en 2025
        SecretSantaAssignment::create([
            'giver_id' => $pastUser->id,
            'receiver_id' => $pastUser->id,
            'family_group_id' => $family2025->id,
        ]);

        // 1. Cargar vista sin parámetros: debe cargar por defecto el año actual (2026) y la familia 2026
        $response = $this->actingAs($admin)->get(route('admin.draw'));
        $response->assertOk();
        $response->assertSee('Yellow Class');
        $response->assertSee('Participante 1');
        $response->assertSee('Participante 2');
        $response->assertSee('Participante 3');
        // No debe cargar por defecto la familia de 2025 ni sus usuarios
        $response->assertDontSee('Usuario Pasado Solo 2025');

        // 2. Realizar sorteo en 2026
        $postResponse = $this->actingAs($admin)->postJson(route('admin.draw.start'), [
            'family_group_id' => $family2026->id,
        ]);
        $postResponse->assertOk()->assertJson(['success' => true]);

        // Verificar que se crearon 3 asignaciones estrictamente para la familia 2026
        $assignments2026 = SecretSantaAssignment::where('family_group_id', $family2026->id)->get();
        $this->assertCount(3, $assignments2026);

        // Nadie se debe haber regalado a sí mismo
        foreach ($assignments2026 as $assignment) {
            $this->assertNotEquals($assignment->giver_id, $assignment->receiver_id);
            $this->assertTrue(in_array($assignment->receiver_id, [$user1->id, $user2->id, $user3->id]));
            $this->assertNotEquals($pastUser->id, $assignment->receiver_id);
        }

        // Las asignaciones de 2025 siguen intactas
        $this->assertEquals(1, SecretSantaAssignment::where('family_group_id', $family2025->id)->count());
    }

    public function test_admin_user_edit_view_provides_year_and_family_group_filters(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $family2025 = FamilyGroup::create([
            'slug' => 'paz2025',
            'name' => 'Familia Paz',
            'year' => 2025,
            'is_active' => true,
        ]);

        $family2026 = FamilyGroup::create([
            'slug' => 'yellow2026',
            'name' => 'Yellow Class',
            'year' => 2026,
            'is_active' => true,
        ]);

        $userJunior = User::factory()->create([
            'name' => 'Junior',
            'phone' => '935442405',
            'dni' => null,
            'family_group_id' => $family2026->id,
        ]);
        $userDaniella = User::factory()->create([
            'name' => 'Daniella',
            'phone' => '933453419',
            'dni' => null,
            'family_group_id' => $family2026->id,
        ]);

        $family2026->users()->attach([$userJunior->id, $userDaniella->id]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $userJunior));
        $response->assertOk();
        // Debe mostrar teléfono en vez de DNI vacío
        $response->assertSee('Teléfono: 935442405');
        // Debe tener los campos de filtrado
        $response->assertSee('Grupo Familiar Interno');
        $response->assertSee('1. Año');
        $response->assertSee('2. Familia General');
        $response->assertSee('3. Participante a vincular');
        $response->assertSee('Yellow Class');
        $response->assertViewHas('availableYears');
        $response->assertViewHas('familyGroupsData');
        $response->assertViewHas('defaultYear', 2026);
        $response->assertViewHas('defaultFamilyId', $family2026->id);
    }

    public function test_internal_family_assignment_and_draw_isolation(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $family2026 = FamilyGroup::create([
            'slug' => 'yellow2026',
            'name' => 'Yellow Class',
            'year' => 2026,
            'is_active' => true,
            'enable_draw_at' => now()->subMinute(),
        ]);

        $junior = User::factory()->create(['name' => 'Junior', 'family_group_id' => $family2026->id]);
        $daniella = User::factory()->create(['name' => 'Daniella', 'family_group_id' => $family2026->id]);
        $userC = User::factory()->create(['name' => 'Participante C', 'family_group_id' => $family2026->id]);
        $userD = User::factory()->create(['name' => 'Participante D', 'family_group_id' => $family2026->id]);

        $family2026->users()->attach([$junior->id, $daniella->id, $userC->id, $userD->id]);

        // 1. Asignar Daniella como familiar de Junior en Yellow Class
        $assignResponse = $this->actingAs($admin)->post(route('admin.users.assign-family', $junior), [
            'family_member_id' => $daniella->id,
            'family_group_id' => $family2026->id,
        ]);
        $assignResponse->assertRedirect();
        $assignResponse->assertSessionHas('success');

        $junior->refresh();
        $daniella->refresh();
        $this->assertNotNull($junior->family_id);
        $this->assertEquals($junior->family_id, $daniella->family_id);
        $this->assertTrue($junior->isFamilyWith($daniella));
        $this->assertTrue($daniella->isFamilyWith($junior));

        // 2. Realizar sorteo de Yellow Class
        $drawResponse = $this->actingAs($admin)->postJson(route('admin.draw.start'), [
            'family_group_id' => $family2026->id,
        ]);
        $drawResponse->assertOk()->assertJson(['success' => true]);

        // Verificar que el sorteo respetó la exclusión interna de familiares
        $assignmentJunior = SecretSantaAssignment::where('family_group_id', $family2026->id)
            ->where('giver_id', $junior->id)
            ->first();
        $assignmentDaniella = SecretSantaAssignment::where('family_group_id', $family2026->id)
            ->where('giver_id', $daniella->id)
            ->first();

        // Junior NO debe regalar a Daniella
        $this->assertNotEquals($daniella->id, $assignmentJunior->receiver_id);
        // Daniella NO debe regalar a Junior
        $this->assertNotEquals($junior->id, $assignmentDaniella->receiver_id);

        // 3. Remover a Daniella del grupo familiar de Junior
        $removeResponse = $this->actingAs($admin)->delete(route('admin.users.remove-family', $junior), [
            'family_member_id' => $daniella->id,
        ]);
        $removeResponse->assertRedirect();
        $removeResponse->assertSessionHas('success');

        $junior->refresh();
        $daniella->refresh();
        $this->assertNull($junior->family_id);
        $this->assertNull($daniella->family_id);
    }

    public function test_draw_fallback_guarantees_bijection_without_duplicates_or_self_gifting(): void
    {
        $users = User::factory()->count(5)->create();
        $controller = new \App\Http\Controllers\Admin\DrawController();
        $refMethod = new \ReflectionMethod($controller, 'createFallbackAssignments');
        $refMethod->setAccessible(true);

        for ($i = 0; $i < 50; $i++) {
            $assignments = $refMethod->invoke($controller, $users);
            
            // Todos los usuarios dan y reciben exactamente un regalo
            $this->assertCount(5, $assignments);
            $this->assertCount(5, array_unique($assignments));

            foreach ($assignments as $giverId => $receiverId) {
                // Nadie se regala a sí mismo
                $this->assertNotEquals($giverId, $receiverId);
                // El receptor existe entre los usuarios
                $this->assertTrue($users->contains('id', $receiverId));
            }
        }
    }
}
