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

    public function index()
    {
        $assignments = SecretSantaAssignment::with(['giver', 'receiver'])->get();
        $hasAssignments = $assignments->isNotEmpty();
        return view('admin.draw', compact('assignments', 'hasAssignments'));
    }

    public function start(Request $request)
    {
        // Check if assignments already exist
        if (SecretSantaAssignment::exists()) {
            return redirect()->route('admin.draw')->with('error', 'El sorteo ya ha sido realizado.');
        }

        $users = User::all();

        if ($users->count() < 2) {
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

            // Prepare success message with family assignment count (without revealing names)
            $message = 'Sorteo realizado exitosamente.';
            if (!empty($this->familyAssignments)) {
                $count = count($this->familyAssignments);
                $message .= " Sin embargo, se realizaron {$count} asignación(es) entre familiares.";
            }

            return redirect()->route('admin.draw')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
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
