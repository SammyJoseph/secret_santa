<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecretSantaAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DrawController extends Controller
{
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

            return redirect()->route('admin.draw');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.draw')->with('error', 'Error al realizar el sorteo. Inténtalo de nuevo.');
        }
    }

    private function performDraw($users)
    {
        $userIds = $users->pluck('id')->toArray();
        $assignments = [];
        $attempts = 0;
        $maxAttempts = 100;

        do {
            $assignments = [];
            $receivers = $userIds;
            shuffle($receivers);

            foreach ($userIds as $giverId) {
                $possibleReceivers = array_filter($receivers, function($receiverId) use ($giverId, $assignments) {
                    return $receiverId !== $giverId && !in_array($receiverId, array_values($assignments));
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

        if (empty($assignments)) {
            throw new \Exception('No se pudo realizar un sorteo válido después de múltiples intentos.');
        }

        return $assignments;
    }
}
