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

        // Create bidirectional relationship
        $user->familyMembers()->attach($familyMember->id);
        $familyMember->familyMembers()->attach($user->id);

        return redirect()->back()->with('success', 'Familiar asignado correctamente.');
    }

    public function remove(Request $request, User $user)
    {
        $request->validate([
            'family_member_id' => 'required|exists:users,id',
        ]);

        $familyMember = User::find($request->family_member_id);

        // Remove bidirectional relationship
        $user->familyMembers()->detach($familyMember->id);
        $familyMember->familyMembers()->detach($user->id);

        return redirect()->back()->with('success', 'Familiar removido correctamente.');
    }
}
