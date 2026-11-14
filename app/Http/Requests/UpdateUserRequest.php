<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        return [
            'name' => 'required|string|min:3|max:255',
            'gift_suggestions' => 'nullable|array',
            'gift_suggestions.*' => 'nullable|string|max:255',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
            'reference_image_path_0' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
            'reference_image_path_1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
            'reference_image_path_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
            'remove_reference_image_0' => 'nullable|boolean',
            'remove_reference_image_1' => 'nullable|boolean',
            'remove_reference_image_2' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'profile_photo_path.image' => 'El archivo de foto de perfil debe ser una imagen válida.',
            'profile_photo_path.mimes' => 'La foto de perfil debe ser de tipo: jpeg, png, jpg, gif, webp o avif.',
            'profile_photo_path.max' => 'La foto de perfil no puede superar los 10 MB.',
            'reference_image_path_0.image' => 'La imagen de la sugerencia 1 debe ser una imagen válida.',
            'reference_image_path_0.mimes' => 'La imagen de la sugerencia 1 debe ser de tipo: jpeg, png, jpg, gif, webp o avif.',
            'reference_image_path_0.max' => 'La imagen de la sugerencia 1 no puede superar los 10 MB.',
            'reference_image_path_1.image' => 'La imagen de la sugerencia 2 debe ser una imagen válida.',
            'reference_image_path_1.mimes' => 'La imagen de la sugerencia 2 debe ser de tipo: jpeg, png, jpg, gif, webp o avif.',
            'reference_image_path_1.max' => 'La imagen de la sugerencia 2 no puede superar los 10 MB.',
            'reference_image_path_2.image' => 'La imagen de la sugerencia 3 debe ser una imagen válida.',
            'reference_image_path_2.mimes' => 'La imagen de la sugerencia 3 debe ser de tipo: jpeg, png, jpg, gif, webp o avif.',
            'reference_image_path_2.max' => 'La imagen de la sugerencia 3 no puede superar los 10 MB.',
        ];
    }
}
