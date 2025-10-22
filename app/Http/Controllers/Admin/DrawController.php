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

    public function index()
    {
        $assignments = SecretSantaAssignment::with(['giver', 'receiver'])->get();
        $hasAssignments = $assignments->isNotEmpty();
        $users = User::all();
        $enableDrawTime = env('SECRET_SANTA_ENABLE_DRAW');
        return view('admin.draw', compact('assignments', 'hasAssignments', 'users', 'enableDrawTime'));
    }

    public function start(Request $request)
    {
        // Check if request wants JSON response
        $wantsJson = $request->wantsJson() || $request->is('api/*') || $request->header('Accept') === 'application/json';

        // Check if assignments already exist
        if (SecretSantaAssignment::exists()) {
            if ($wantsJson) {
                return response()->json(['error' => 'El sorteo ya ha sido realizado.'], 400);
            }
            return redirect()->route('admin.draw')->with('error', 'El sorteo ya ha sido realizado.');
        }

        $users = User::all();

        if ($users->count() < 2) {
            if ($wantsJson) {
                return response()->json(['error' => 'Se necesitan al menos 2 usuarios para realizar el sorteo.'], 400);
            }
            return redirect()->route('admin.draw')->with('error', 'Se necesitan al menos 2 usuarios para realizar el sorteo.');
        }

        try {
            DB::beginTransaction();

            $assignments = $this->performDraw($users);

            foreach ($assignments as $giverId => $receiverId) {
                SecretSantaAssignment::create([
                    'giver_id' => $giverId,
                    'receiver_id' => $receiverId,
                ]);
            }

            DB::commit();

            // Log the number of attempts for successful draw
            Log::info('Sorteo realizado exitosamente tras ' . $this->attempts . ' intento(s).');

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

            return redirect()->route('admin.draw');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($wantsJson) {
                return response()->json(['error' => 'Error al realizar el sorteo. Inténtalo de nuevo.'], 500);
            }
            return redirect()->route('admin.draw')->with('error', 'Error al realizar el sorteo. Inténtalo de nuevo.');
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
