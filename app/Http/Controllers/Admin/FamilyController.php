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
            'family_group_id' => 'nullable|exists:family_groups,id',
        ]);

        $familyMember = User::find($request->family_member_id);

        // Validar pertenencia a la familia general si fue enviada
        if ($request->filled('family_group_id')) {
            $familyGroupId = $request->input('family_group_id');
            $belongsToGroup = DB::table('family_group_user')
                ->where('family_group_id', $familyGroupId)
                ->where('user_id', $familyMember->id)
                ->exists() || $familyMember->family_group_id == $familyGroupId;

            if (!$belongsToGroup) {
                return redirect()->back()->with('error', 'El participante seleccionado no pertenece a la familia indicada.');
            }
        }

        // Check if relationship already exists
        if ($user->isFamilyWith($familyMember)) {
            return redirect()->back()->with('error', 'Esta relación familiar ya existe.');
        }

        // Determinar el family_id a usar: priorizar el del usuario actual, luego el del familiar, luego nuevo id
        $targetFamilyId = $user->family_id ?? $familyMember->family_id ?? $user->id;

        $oldUserFamilyId = $user->family_id;
        $oldMemberFamilyId = $familyMember->family_id;

        // Si alguno ya tenía un grupo previo diferente, unificar a todos los miembros de ese grupo
        if ($oldUserFamilyId && $oldUserFamilyId !== $targetFamilyId) {
            User::where('family_id', $oldUserFamilyId)->update(['family_id' => $targetFamilyId]);
        }
        if ($oldMemberFamilyId && $oldMemberFamilyId !== $targetFamilyId) {
            User::where('family_id', $oldMemberFamilyId)->update(['family_id' => $targetFamilyId]);
        }

        // Assign both users to the determined family_id
        $user->family_id = $targetFamilyId;
        $familyMember->family_id = $targetFamilyId;
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
