<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FamilyGroupController extends Controller
{
    /**
     * Display a listing of family groups.
     */
    public function index()
    {
        $familyGroups = FamilyGroup::withCount(['users', 'assignments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.family-groups.index', compact('familyGroups'));
    }

    /**
     * Show the form for creating a new family group.
     */
    public function create()
    {
        return view('admin.family-groups.create');
    }

    /**
     * Store a newly created family group in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => [
                'required',
                'alpha_dash',
                'max:50',
                'unique:family_groups,slug',
                'not_in:default,admin,api,sanctum'
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'enable_draw_at' => 'required|date|after:now',
            'reveal_date' => 'required|date|after:enable_draw_at',
            'profile_edit_end_date' => 'required|date|after:reveal_date',
        ], [
            'slug.required' => 'El identificador es obligatorio',
            'slug.alpha_dash' => 'El identificador solo puede contener letras, números, guiones y guiones bajos',
            'slug.unique' => 'Este identificador ya está en uso',
            'slug.not_in' => 'Este identificador está reservado',
            'name.required' => 'El nombre es obligatorio',
            'enable_draw_at.required' => 'La fecha de sorteo es obligatoria',
            'enable_draw_at.after' => 'La fecha de sorteo debe ser futura',
            'reveal_date.required' => 'La fecha de revelación es obligatoria',
            'reveal_date.after' => 'La fecha de revelación debe ser posterior a la fecha de sorteo',
            'profile_edit_end_date.required' => 'La fecha límite de edición es obligatoria',
            'profile_edit_end_date.after' => 'La fecha límite debe ser posterior a la fecha de revelación',
        ]);

        FamilyGroup::create($validated);

        return redirect()->route('admin.family-groups.index')
            ->with('success', 'Familia creada exitosamente. Comparte el enlace de registro con los participantes.');
    }

    /**
     * Display the specified family group.
     */
    public function show(FamilyGroup $familyGroup)
    {
        $familyGroup->loadCount(['users', 'assignments']);
        
        return view('admin.family-groups.show', compact('familyGroup'));
    }

    /**
     * Show the form for editing the specified family group.
     */
    public function edit(FamilyGroup $familyGroup)
    {
        return view('admin.family-groups.edit', compact('familyGroup'));
    }

    /**
     * Update the specified family group in storage.
     */
    public function update(Request $request, FamilyGroup $familyGroup)
    {
        // Si la familia ya fue sorteada, solo permitir editar profile_edit_end_date
        if ($familyGroup->hasDrawn()) {
            $validated = $request->validate([
                'profile_edit_end_date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($familyGroup) {
                        if ($familyGroup->reveal_date && \Carbon\Carbon::parse($value)->lte($familyGroup->reveal_date)) {
                            $fail('La fecha límite debe ser posterior a la fecha de revelación.');
                        }
                    },
                ],
            ], [
                'profile_edit_end_date.required' => 'La fecha límite de edición es obligatoria',
            ]);

            $familyGroup->update([
                'profile_edit_end_date' => $validated['profile_edit_end_date']
            ]);

            return redirect()->route('admin.family-groups.show', $familyGroup)
                ->with('success', 'Fecha límite de edición de perfil actualizada.');
        }

        $validated = $request->validate([
            'slug' => [
                'required',
                'alpha_dash',
                'max:50',
                Rule::unique('family_groups')->ignore($familyGroup->id),
                'not_in:default,admin,api,sanctum'
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'enable_draw_at' => 'required|date',
            'reveal_date' => 'required|date|after:enable_draw_at',
            'profile_edit_end_date' => 'required|date|after:reveal_date',
            'is_active' => 'boolean',
        ], [
            'slug.required' => 'El identificador es obligatorio',
            'slug.alpha_dash' => 'El identificador solo puede contener letras, números, guiones y guiones bajos',
            'slug.unique' => 'Este identificador ya está en uso',
            'slug.not_in' => 'Este identificador está reservado',
            'name.required' => 'El nombre es obligatorio',
            'enable_draw_at.required' => 'La fecha de sorteo es obligatoria',
            'reveal_date.required' => 'La fecha de revelación es obligatoria',
            'reveal_date.after' => 'La fecha de revelación debe ser posterior a la fecha de sorteo',
            'profile_edit_end_date.required' => 'La fecha límite de edición es obligatoria',
            'profile_edit_end_date.after' => 'La fecha límite debe ser posterior a la fecha de revelación',
        ]);

        $familyGroup->update($validated);

        return redirect()->route('admin.family-groups.show', $familyGroup)
            ->with('success', 'Familia actualizada exitosamente.');
    }

    /**
     * Remove the specified family group from storage.
     */
    public function destroy(FamilyGroup $familyGroup)
    {
        // Proteger familia default
        if ($familyGroup->isDefault()) {
            return redirect()->route('admin.family-groups.index')
                ->with('error', 'La familia original no puede ser eliminada.');
        }

        // Verificar si tiene usuarios registrados
        if ($familyGroup->users()->count() > 0) {
            return redirect()->route('admin.family-groups.index')
                ->with('error', 'No se puede eliminar una familia que tiene usuarios registrados. Considere desactivarla en su lugar.');
        }

        $familyGroup->delete();

        return redirect()->route('admin.family-groups.index')
            ->with('success', 'Familia eliminada exitosamente.');
    }
}