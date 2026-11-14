<?php

namespace Database\Seeders;

use App\Models\FamilyGroup;
use App\Models\GiftSuggestion;
use App\Models\SecretSantaAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimulatedDrawSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = (int) date('Y');

        // 1. Crear o actualizar la familia simulada
        $familyGroup = FamilyGroup::updateOrCreate(
            ['slug' => 'familia-demo'],
            [
                'name' => 'Familia Demo Navideña',
                'description' => 'Familia de prueba con sorteo realizado para ver el lightbox slider y botón festivo.',
                'year' => $currentYear,
                'is_active' => true,
                'enable_draw_at' => now()->subDays(2),
                'reveal_date' => now()->subHours(2), // Ya revelado
                'profile_edit_end_date' => now()->addDays(30),
            ]
        );

        $this->command?->info("Familia '{$familyGroup->name}' (ID: {$familyGroup->id}) configurada con sorteo revelado.");

        // 2. Obtener o crear al usuario administrador principal (Sammy / Sam)
        $mainUser = User::find(1);
        if (!$mainUser) {
            $mainUser = User::create([
                'name' => 'Sammy',
                'nickname' => 'Sam',
                'dni' => '47003307',
                'phone' => '987654321',
                'email' => 'sam@artisam.dev',
                'is_admin' => true,
                'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'password')),
                'profile_photo_path' => 'profile-photos/1_85U95M6PAf.jpg',
            ]);
        }

        // Asignar al usuario principal a la familia simulada
        $mainUser->update(['family_group_id' => $familyGroup->id]);
        $familyGroup->users()->syncWithoutDetaching([$mainUser->id]);

        // 3. Crear o actualizar 7 usuarios ficticios
        $mockUsersData = [
            [
                'name' => 'María García',
                'nickname' => 'Mari',
                'dni' => '80000001',
                'phone' => '987000001',
                'photo' => 'profile-photos/10_sjpbgfvtW9.jpg',
                'has_suggestions' => false, // Será el amigo secreto de Sammy (sin sugerencias para ver ❓)
            ],
            [
                'name' => 'Carlos Mendoza',
                'nickname' => 'Carlitos',
                'dni' => '80000002',
                'phone' => '987000002',
                'photo' => 'profile-photos/10_g99zVqkbZ6.jpg',
                'has_suggestions' => true,
                'suggestions' => [
                    'Un libro de ciencia ficción o tecnología',
                    'Auriculares inalámbricos para correr',
                ],
            ],
            [
                'name' => 'Lucía Fernández',
                'nickname' => 'Lu',
                'dni' => '80000003',
                'phone' => '987000003',
                'photo' => 'profile-photos/10_I27WdpSMHP.jpg',
                'has_suggestions' => true,
                'suggestions' => [
                    'Taza térmica para café o té',
                    'Velas aromáticas con esencia de vainilla',
                ],
            ],
            [
                'name' => 'Andrés Torres',
                'nickname' => 'Andy',
                'dni' => '80000004',
                'phone' => '987000004',
                'photo' => 'profile-photos/11_cWqMgPy5l4.jpg',
                'has_suggestions' => true,
                'suggestions' => [
                    'Camiseta de fútbol talla M',
                    'Juego de mesa estratégico',
                ],
            ],
            [
                'name' => 'Valeria Gómez',
                'nickname' => 'Vale',
                'dni' => '80000005',
                'phone' => '987000005',
                'photo' => 'profile-photos/11_Q65ptl7U1f.jpg',
                'has_suggestions' => true,
                'suggestions' => [
                    'Cuaderno o libreta con diseño navideño',
                ],
            ],
            [
                'name' => 'Diego Castro',
                'nickname' => 'Dieguito',
                'dni' => '80000006',
                'phone' => '987000006',
                'photo' => 'profile-photos/15_XshFlSCUQk.jpg',
                'has_suggestions' => true,
                'suggestions' => [
                    'Gorra deportiva o lentes de sol',
                ],
            ],
            [
                'name' => 'Sofía Morales',
                'nickname' => 'Sofi',
                'dni' => '80000007',
                'phone' => '987000007',
                'photo' => 'profile-photos/1_0iIHEqGeni.jpg',
                'has_suggestions' => true,
                'suggestions' => [
                    'Chocolates finos o postre navideño',
                ],
            ],
        ];

        $simulatedUsers = [];
        foreach ($mockUsersData as $data) {
            $user = User::updateOrCreate(
                ['dni' => $data['dni']],
                [
                    'name' => $data['name'],
                    'nickname' => $data['nickname'],
                    'phone' => $data['phone'],
                    'email' => strtolower($data['nickname']) . '@test.local',
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                    'profile_photo_path' => $data['photo'],
                    'family_group_id' => $familyGroup->id,
                ]
            );

            $familyGroup->users()->syncWithoutDetaching([$user->id]);

            // Gestionar sugerencias
            GiftSuggestion::where('user_id', $user->id)
                ->where('family_group_id', $familyGroup->id)
                ->delete();

            if (!empty($data['has_suggestions']) && !empty($data['suggestions'])) {
                foreach ($data['suggestions'] as $suggestionText) {
                    GiftSuggestion::create([
                        'user_id' => $user->id,
                        'family_group_id' => $familyGroup->id,
                        'suggestion' => $suggestionText,
                    ]);
                }
            }

            $simulatedUsers[] = $user;
        }

        // 4. Limpiar asignaciones previas en este grupo
        SecretSantaAssignment::where('family_group_id', $familyGroup->id)->delete();

        // 5. Crear ciclo de asignaciones de Amigo Secreto:
        // Sammy -> María García (sin sugerencias -> verá ❓)
        // María García -> Carlos -> Lucía -> Andrés -> Valeria -> Diego -> Sofía -> Sammy
        $cycle = array_merge([$mainUser], $simulatedUsers);
        $totalMembers = count($cycle);

        for ($i = 0; $i < $totalMembers; $i++) {
            $giver = $cycle[$i];
            $receiver = $cycle[($i + 1) % $totalMembers];

            SecretSantaAssignment::create([
                'family_group_id' => $familyGroup->id,
                'giver_id' => $giver->id,
                'receiver_id' => $receiver->id,
            ]);
        }

        $this->command?->info("Sorteo simulado completado con {$totalMembers} participantes.");
        $this->command?->info("Usuario principal '{$mainUser->name}' asignado a regalar a '{$simulatedUsers[0]->name}' (sin sugerencias).");
    }
}
