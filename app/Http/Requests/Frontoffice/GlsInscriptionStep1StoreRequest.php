<?php

namespace App\Http\Requests\Frontoffice;

use Illuminate\Foundation\Http\FormRequest;

class GlsInscriptionStep1StoreRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'nom'     => 'required|string|max:255',
            'prenom'  => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'required|string|max:20',
            'adresse' => 'required|string|max:500',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'nom.required'     => 'Le nom est obligatoire.',
            'prenom.required'  => 'Le prénom est obligatoire.',
            'email.required'   => 'L\'email est obligatoire.',
            'email.email'      => 'L\'email doit être valide.',
            'phone.required'   => 'Le téléphone est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
        ];
    }
}
