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
        // Obtener todas las familias para el selector
        $familyGroups = \App\Models\FamilyGroup::withCount('users')->get();
        
        // Obtener familia seleccionada (por defecto la primera)
        $selectedFamilyGroupId = $request->get('family_group_id', 1);
        $selectedFamilyGroup = \App\Models\FamilyGroup::find($selectedFamilyGroupId);
        
        if (!$selectedFamilyGroup) {
            $selectedFamilyGroup = \App\Models\FamilyGroup::first();
            $selectedFamilyGroupId = $selectedFamilyGroup->id;
        }
        
        // Filtrar assignments y usuarios por familia seleccionada
        $assignments = SecretSantaAssignment::with(['giver', 'receiver'])
            ->where('family_group_id', $selectedFamilyGroupId)
            ->get();
        
        $hasAssignments = $assignments->isNotEmpty();
        
        $users = User::where('family_group_id', $selectedFamilyGroupId)->get();
        
        // Usar fechas de la familia seleccionada
        $enableDrawTime = $selectedFamilyGroup->enable_draw_at;
        
        return view('admin.draw', compact(
            'assignments',
            'hasAssignments',
            'users',
            'enableDrawTime',
            'familyGroups',
            'selectedFamilyGroup',
            'selectedFamilyGroupId'
        ));
    }

    public function start(Request $request)
    {
        // Check if request wants JSON response
        $wantsJson = $request->wantsJson() || $request->is('api/*') || $request->header('Accept') === 'application/json';

        // Obtener family_group_id del request
        $familyGroupId = $request->input('family_group_id', 1);
        $familyGroup = \App\Models\FamilyGroup::findOrFail($familyGroupId);

        // Check if assignments already exist for this family
        if ($familyGroup->hasDrawn()) {
            if ($wantsJson) {
                return response()->json(['error' => 'El sorteo para esta familia ya ha sido realizado.'], 400);
            }
            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId])
                ->with('error', 'El sorteo para esta familia ya ha sido realizado.');
        }

        // Verificar que sea tiempo de sortear
        if (!$familyGroup->canDraw()) {
            if ($wantsJson) {
                return response()->json(['error' => 'Aún no es tiempo de realizar el sorteo para esta familia.'], 400);
            }
            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId])
                ->with('error', 'Aún no es tiempo de realizar el sorteo para esta familia.');
        }

        // Filtrar usuarios solo de esta familia
        $users = User::where('family_group_id', $familyGroupId)->get();

        if ($users->count() < 2) {
            if ($wantsJson) {
                return response()->json(['error' => 'Se necesitan al menos 2 usuarios para realizar el sorteo.'], 400);
            }
            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId])
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

            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId]);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($wantsJson) {
                return response()->json(['error' => 'Error al realizar el sorteo. Inténtalo de nuevo.'], 500);
            }
            return redirect()->route('admin.draw', ['family_group_id' => $familyGroupId])
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
        $maxAttempts = 20; // Reduced to prevent timeout

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
        $assignments = [];

        // Simple round-robin assignment as fallback
        $receivers = $userIds;
        shuffle($receivers);

        // Ensure no one gets themselves
        foreach ($userIds as $index => $giverId) {
            $receiverId = $receivers[$index];
            if ($receiverId === $giverId) {
                // Swap with next if possible
                $nextIndex = ($index + 1) % count($receivers);
                if ($receivers[$nextIndex] !== $giverId) {
                    $temp = $receivers[$index];
                    $receivers[$index] = $receivers[$nextIndex];
                    $receivers[$nextIndex] = $temp;
                    $receiverId = $receivers[$index];
                }
            }
            $assignments[$giverId] = $receiverId;
        }

        return $assignments;
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
