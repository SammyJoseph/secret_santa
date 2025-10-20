<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FamilyController extends Controller
{
    public function assign(Request $request, User $user)
    {
        $request->validate([
            'family_member_id' => 'required|exists:users,id|different:id',
        ]);

        $familyMember = User::find($request->family_member_id);

        // Check if relationship already exists
        if ($user->isFamilyWith($familyMember)) {
            return redirect()->back()->with('error', 'Esta relación familiar ya existe.');
        }

        // Determine the family_id to use: prioritize existing family of familyMember, then user's, then create new
        $familyId = $familyMember->family_id ?? $user->family_id ?? $user->id;

        // Assign both users to the determined family_id
        $user->family_id = $familyId;
        $familyMember->family_id = $familyId;
        $user->save();
        $familyMember->save();

        return redirect()->back()->with('success', 'Familiar asignado correctamente.');
    }

    public function remove(Request $request, User $user)
    {
        $request->validate([
            'family_member_id' => 'required|exists:users,id',
        ]);

        $familyMember = User::find($request->family_member_id);

        // Set familyMember's family_id to null
        $familyMember->family_id = null;
        $familyMember->save();

        // If the user who initiated the removal now has no family members left, set their family_id to null
        $remainingFamily = User::where('family_id', $user->family_id)->where('id', '!=', $user->id)->count();
        if ($remainingFamily == 0) {
            $user->family_id = null;
            $user->save();
        }

        return redirect()->back()->with('success', 'Familiar removido correctamente.');
    }
}
