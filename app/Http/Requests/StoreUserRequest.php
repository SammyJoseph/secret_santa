<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('phone') && $this->filled('dni')) {
            $this->merge(['phone' => $this->dni]);
        }
    }

    public function rules(): array
    {
        $targetFamilyId = session('registration_family_group_id', 1);
        $targetFamily = \App\Models\FamilyGroup::find($targetFamilyId);
        $targetYear = $targetFamily ? $targetFamily->year : (int) date('Y');

        return [
            'name' => 'required|string|min:2|max:255',
            'phone' => [
                'required',
                'string',
                'regex:/^\d{8,9}$/',
                function ($attribute, $value, $fail) use ($targetYear) {
                    // Si se envía migrated_dni, el teléfono no debe pertenecer a OTRO usuario
                    $query = \App\Models\User::where('phone', $value);
                    if ($this->filled('migrated_dni')) {
                        $query->where('dni', '!=', $this->migrated_dni);
                    }
                    $otherUser = $query->first();
                    if ($otherUser) {
                        $fail('Este número de celular ya está registrado por otro participante.');
                        return;
                    }

                    // Chequear si el usuario ya está registrado en este año
                    $existingUser = null;
                    if ($this->filled('migrated_dni')) {
                        $existingUser = \App\Models\User::where('dni', $this->migrated_dni)->with(['familyGroup', 'familyGroups'])->first();
                    } elseif (strlen($value) === 8) {
                        $existingUser = \App\Models\User::where('dni', $value)->with(['familyGroup', 'familyGroups'])->first();
                    } else {
                        $existingUser = \App\Models\User::where('phone', $value)->with(['familyGroup', 'familyGroups'])->first();
                    }

                    if ($existingUser) {
                        $currentYearFamily = $existingUser->familyGroups()->where('year', $targetYear)->first()
                            ?? ($existingUser->familyGroup && $existingUser->familyGroup->year == $targetYear ? $existingUser->familyGroup : null);

                        if ($currentYearFamily) {
                            $fail("Este participante ya está registrado en la familia '{$currentYearFamily->name}' para la edición de este año.");
                        }
                    }
                },
            ],
            'migrated_dni' => 'nullable|string|digits:8|exists:users,dni',
            'email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    $query = \App\Models\User::where('email', $value);
                    if ($this->filled('migrated_dni')) {
                        $query->where('dni', '!=', $this->migrated_dni);
                    } elseif ($this->filled('phone')) {
                        $query->where('phone', '!=', $this->phone)->where('dni', '!=', $this->phone);
                    }
                    $existingUser = $query->first();
                    if ($existingUser) {
                        $fail('El correo electrónico ya está en uso por otro participante.');
                    }
                },
            ],
            'password' => 'required|string|min:4|confirmed',
            'gift_suggestions' => 'nullable|array',
            'gift_suggestions.*' => 'nullable|string|min:3|max:255',
            'profile_photo_path' => 'nullable|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'phone.required' => 'El número de celular o DNI es obligatorio.',
            'phone.regex' => 'Debe contener 9 dígitos (celular) u 8 dígitos (DNI).',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 4 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
