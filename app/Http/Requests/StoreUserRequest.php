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
    public function rules(): array
    {
        $targetFamilyId = session('registration_family_group_id', 1);
        $targetFamily = \App\Models\FamilyGroup::find($targetFamilyId);
        $targetYear = $targetFamily ? $targetFamily->year : (int) date('Y');

        return [
            'name' => 'required|string|min:2|max:255',
            'dni' => [
                'required',
                'string',
                'digits:8',
                function ($attribute, $value, $fail) use ($targetYear) {
                    $existingUser = \App\Models\User::where('dni', $value)->with('familyGroup')->first();
                    if ($existingUser && $existingUser->familyGroup) {
                        if ($existingUser->familyGroup->year == $targetYear) {
                            $fail("Este DNI ya está registrado en la familia '{$existingUser->familyGroup->name}' para la edición de este año.");
                        }
                    }
                },
            ],
            'email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    $existingUser = \App\Models\User::where('email', $value)->where('dni', '!=', $this->dni)->first();
                    if ($existingUser) {
                        $fail('El correo electrónico ya está en uso por otro participante.');
                    }
                },
            ],
            'password' => 'required|string|min:4|confirmed',
            'gift_suggestions' => 'required|array|min:3|max:3',
            'gift_suggestions.*' => 'required|string|min:3|max:255',
            'profile_photo_path' => 'nullable|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
        ];
    }
}
