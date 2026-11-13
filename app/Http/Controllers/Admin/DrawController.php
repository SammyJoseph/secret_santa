<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecretSantaAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DrawController extends Controller
{
    private $familyAssignments = [];
    private $attempts = 0;

    public function index(Request $request)
    {
        $currentYear = (int) date('Y');
        $selectedYear = $request->get('year', $currentYear);

        if (is_numeric($selectedYear)) {
            $selectedYear = (int) $selectedYear;
        }

        // Obtener todos los años disponibles en FamilyGroup (empezando desde 2025)
        $familyYears = \App\Models\FamilyGroup::select('year')
            ->distinct()
            ->whereNotNull('year')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->toArray();
        $availableYears = array_values(array_unique(array_merge([$currentYear, 2025], $familyYears)));
        rsort($availableYears);

        // Obtener familias filtradas por el año seleccionado
        $familyGroupsQuery = \App\Models\FamilyGroup::withCount('users')->orderBy('name', 'asc');
        if ($selectedYear !== 'all') {
            $familyGroupsQuery->where('year', $selectedYear);
        }
        $familyGroups = $familyGroupsQuery->get();

        // Obtener familia seleccionada (por defecto la primera del año seleccionado)
        $selectedFamilyGroupId = $request->get('family_group_id');
        $selectedFamilyGroup = null;

        if ($selectedFamilyGroupId) {
            $selectedFamilyGroup = $familyGroups->firstWhere('id', (int) $selectedFamilyGroupId);
        }

        if (!$selectedFamilyGroup && $familyGroups->isNotEmpty()) {
            $selectedFamilyGroup = $familyGroups->first();
            $selectedFamilyGroupId = $selectedFamilyGroup->id;
        }

        $assignments = collect();
        $hasAssignments = false;
        $users = collect();
        $enableDrawTime = null;

        if ($selectedFamilyGroup) {
            // Filtrar assignments y usuarios por familia seleccionada
            $assignments = SecretSantaAssignment::with(['giver', 'receiver'])
                ->where('family_group_id', $selectedFamilyGroup->id)
                ->get();

            $hasAssignments = $assignments->isNotEmpty();
            $users = $selectedFamilyGroup->users;
            $enableDrawTime = $selectedFamilyGroup->enable_draw_at;
        }

        return view('admin.draw', compact(
            'assignments',
            'hasAssignments',
            'users',
            'enableDrawTime',
            'familyGroups',
            'selectedFamilyGroup',
            'selectedFamilyGroupId',
            'availableYears',
            'selectedYear'
        ));
    }

    public function start(Request $request)
    {
        // Check if request wants JSON response
        $wantsJson = $request->wantsJson() || $request->is('api/*') || $request->header('Accept') === 'application/json';

        // Obtener family_group_id del request
        $familyGroupId = $request->input('family_group_id');
        if (!$familyGroupId) {
            if ($wantsJson) {
                return response()->json(['error' => 'ID de familia requerido.'], 400);
            }
            return redirect()->route('admin.draw')->with('error', 'Debes seleccionar una familia.');
        }

        $familyGroup = \App\Models\FamilyGroup::findOrFail($familyGroupId);

        // Check if assignments already exist for this family
        if ($familyGroup->hasDrawn()) {
            if ($wantsJson) {
                return response()->json(['error' => 'El sorteo para esta familia ya ha sido realizado.'], 400);
            }
            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId, 'year' => $familyGroup->year])
                ->with('error', 'El sorteo para esta familia ya ha sido realizado.');
        }

        // Verificar que sea tiempo de sortear
        if (!$familyGroup->canDraw()) {
            if ($wantsJson) {
                return response()->json(['error' => 'Aún no es tiempo de realizar el sorteo para esta familia.'], 400);
            }
            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId, 'year' => $familyGroup->year])
                ->with('error', 'Aún no es tiempo de realizar el sorteo para esta familia.');
        }

        // Filtrar usuarios solo de esta familia
        $users = $familyGroup->users;

        if ($users->count() < 2) {
            if ($wantsJson) {
                return response()->json(['error' => 'Se necesitan al menos 2 usuarios para realizar el sorteo.'], 400);
            }
            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId, 'year' => $familyGroup->year])
                ->with('error', 'Se necesitan al menos 2 usuarios para realizar el sorteo.');
        }

        try {
            DB::beginTransaction();

            $assignments = $this->performDraw($users);

            foreach ($assignments as $giverId => $receiverId) {
                SecretSantaAssignment::create([
                    'giver_id' => $giverId,
                    'receiver_id' => $receiverId,
                    'family_group_id' => $familyGroupId,
                ]);
            }

            DB::commit();

            // Log the number of attempts for successful draw
            Log::info('Sorteo realizado exitosamente tras ' . $this->attempts . ' intento(s).');

            // Log if family assignments were necessary
            if (empty($this->familyAssignments)) {
                Log::info('No fue necesario asignar participantes de un mismo grupo familiar.');
            } else {
                Log::info('Se tuvieron que asignar participantes de un mismo grupo familiar: ' . count($this->familyAssignments) . ' asignación(es).');
            }

            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'users' => $users->map(function($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'imagen' => $user->profile_photo_url
                        ];
                    })
                ]);
            }

            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId, 'year' => $familyGroup->year]);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($wantsJson) {
                return response()->json(['error' => 'Error al realizar el sorteo. Inténtalo de nuevo.'], 500);
            }
            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId, 'year' => $familyGroup->year])
                ->with('error', 'Error al realizar el sorteo. Inténtalo de nuevo.');
        }
    }

    public function reset(Request $request)
    {
        // Check if request wants JSON response
        $wantsJson = $request->wantsJson() || $request->is('api/*') || $request->header('Accept') === 'application/json';

        $familyGroupId = $request->input('family_group_id');
        
        if (!$familyGroupId) {
            if ($wantsJson) {
                return response()->json(['error' => 'ID de familia requerido.'], 400);
            }
            return back()->with('error', 'ID de familia requerido.');
        }

        $familyGroup = \App\Models\FamilyGroup::findOrFail($familyGroupId);

        if (!$familyGroup->hasDrawn()) {
            if ($wantsJson) {
                return response()->json(['error' => 'Esta familia no tiene un sorteo realizado.'], 400);
            }
            return back()->with('error', 'Esta familia no tiene un sorteo realizado.');
        }

        try {
            DB::beginTransaction();

            // Delete all assignments for this family
            SecretSantaAssignment::where('family_group_id', $familyGroupId)->delete();

            DB::commit();

            Log::info('Sorteo deshecho para la familia: ' . $familyGroup->name);

            if ($wantsJson) {
                return response()->json(['success' => true, 'message' => 'Sorteo deshecho correctamente.']);
            }

            return redirect()->route('admin.family-groups.edit', $familyGroup)
                ->with('success', 'El sorteo ha sido deshecho correctamente. Ahora puedes agregar más participantes y volver a sortear.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al deshacer sorteo: ' . $e->getMessage());
            
            if ($wantsJson) {
                return response()->json(['error' => 'Error al deshacer el sorteo.'], 500);
            }
            return back()->with('error', 'Error al deshacer el sorteo.');
        }
    }

    private function performDraw($users)
    {
        $userIds = $users->pluck('id')->toArray();
        $assignments = [];
        $familyAssignments = [];
        $attempts = 0;
        $maxAttempts = 50; // Aumentado para dar margen de búsqueda rápida (toma < 5ms)

        // First attempt: Try to avoid family assignments
        do {
            $assignments = [];
            $receivers = $userIds;
            shuffle($receivers);

            foreach ($userIds as $giverId) {
                $possibleReceivers = array_filter($receivers, function($receiverId) use ($giverId, $assignments, $users) {
                    $giver = $users->find($giverId);
                    $receiver = $users->find($receiverId);

                    // No se puede asignar a sí mismo
                    if ($receiverId === $giverId) {
                        return false;
                    }

                    // Ya está asignado
                    if (in_array($receiverId, array_values($assignments))) {
                        return false;
                    }

                    // Priorizar no asignar a familiares (pero permitir si es necesario)
                    if ($giver->isFamilyWith($receiver)) {
                        return false;
                    }

                    return true;
                });

                if (empty($possibleReceivers)) {
                    $assignments = [];
                    break;
                }

                $receiverId = $possibleReceivers[array_rand($possibleReceivers)];
                $assignments[$giverId] = $receiverId;
                $receivers = array_diff($receivers, [$receiverId]);
            }

            $attempts++;
        } while (empty($assignments) && $attempts < $maxAttempts);

        // If no valid assignment found, allow family assignments as fallback
        if (empty($assignments)) {
            // Use a more efficient algorithm for fallback
            $assignments = $this->createFallbackAssignments($users);
            $familyAssignments = $this->getFamilyAssignmentsFromResults($assignments, $users);
            $this->attempts = $maxAttempts + 1;
        } else {
            $this->attempts = $attempts;
        }

        if (empty($assignments)) {
            throw new \Exception('No se pudo realizar un sorteo válido después de múltiples intentos.');
        }

        // Store family assignments for notification
        $this->familyAssignments = $familyAssignments;

        return $assignments;
    }

    private function createFallbackAssignments($users)
    {
        $userIds = $users->pluck('id')->toArray();
        $count = count($userIds);

        if ($count < 2) {
            return [];
        }

        // Permutación cíclica (Hamiltonian Cycle) que garantiza matemáticamente:
        // 1. Nadie se regala a sí mismo jamás
        // 2. Todos reciben exactamente a 1 persona y dan a 1 persona (sin duplicados ni omisiones)
        // 3. Minimiza las colisiones familiares buscando la mejor combinación entre 100 intentos
        $bestAssignments = [];
        $minConflicts = PHP_INT_MAX;

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $order = $userIds;
            shuffle($order);

            $conflicts = 0;
            $currentAssignments = [];
            for ($i = 0; $i < $count; $i++) {
                $giverId = $order[$i];
                $receiverId = $order[($i + 1) % $count];
                $currentAssignments[$giverId] = $receiverId;

                $giver = $users->find($giverId);
                $receiver = $users->find($receiverId);
                if ($giver && $receiver && $giver->isFamilyWith($receiver)) {
                    $conflicts++;
                }
            }

            if ($conflicts < $minConflicts) {
                $minConflicts = $conflicts;
                $bestAssignments = $currentAssignments;
            }

            // Si encontramos una con 0 conflictos familiares, terminar inmediatamente
            if ($minConflicts === 0) {
                break;
            }
        }

        return $bestAssignments;
    }

    private function getFamilyAssignmentsFromResults($assignments, $users)
    {
        $familyAssignments = [];

        foreach ($assignments as $giverId => $receiverId) {
            $giver = $users->find($giverId);
            $receiver = $users->find($receiverId);

            if ($giver && $receiver && $giver->isFamilyWith($receiver)) {
                $familyAssignments[] = [
                    'giver' => $giver,
                    'receiver' => $receiver
                ];
            }
        }

        return $familyAssignments;
    }
}
