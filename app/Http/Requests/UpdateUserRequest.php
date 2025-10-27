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
            'gift_suggestions' => 'required|array|min:1|max:3',
            'gift_suggestions.*' => 'required|string|max:255',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
        ];
    }
}
