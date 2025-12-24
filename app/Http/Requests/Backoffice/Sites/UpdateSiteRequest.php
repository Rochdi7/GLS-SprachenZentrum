<?php

namespace App\Http\Requests\Backoffice\Sites;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'city'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255',

            'is_active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du site est obligatoire.',
            'city.required' => 'La ville est obligatoire.',
            'email.email'   => 'Adresse email invalide.',
        ];
    }
}
