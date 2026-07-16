<?php

namespace App\Http\Requests\Frontoffice;

use Illuminate\Foundation\Http\FormRequest;

class GlsInscriptionStoreRequest extends FormRequest
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
        $rules = [
            'nom'             => 'required|string|max:255',
            'prenom'          => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'required|string|max:20',
            'adresse'         => 'required|string|max:500',
            'type_cours'      => 'required|in:presentiel,en_ligne',
            'niveau'          => 'required|in:A0,A1,A2,B1,B2,C1',
            'horaire_prefere' => 'nullable|string|max:50',
            'date_start'      => 'nullable|date',
            'group_id'        => 'required|integer|min:1|max:999',
            'form_source'     => 'nullable|string|max:50',
        ];

        // Centre validation depends on type_cours
        if ($this->input('type_cours') === 'presentiel') {
            $rules['centre'] = 'required|integer|exists:sites,id';
        } else {
            // For en_ligne, centre is not required
            $rules['centre'] = 'nullable|integer';
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'nom.required'             => 'Le nom est obligatoire.',
            'nom.string'               => 'Le nom doit être un texte.',
            'nom.max'                  => 'Le nom ne peut pas dépasser 255 caractères.',

            'prenom.required'          => 'Le prénom est obligatoire.',
            'prenom.string'            => 'Le prénom doit être un texte.',
            'prenom.max'               => 'Le prénom ne peut pas dépasser 255 caractères.',

            'email.required'           => 'L\'email est obligatoire.',
            'email.email'              => 'L\'email doit être valide.',
            'email.max'                => 'L\'email ne peut pas dépasser 255 caractères.',

            'phone.required'           => 'Le téléphone est obligatoire.',
            'phone.string'             => 'Le téléphone doit être un texte.',
            'phone.max'                => 'Le téléphone ne peut pas dépasser 20 caractères.',

            'adresse.required'         => 'L\'adresse est obligatoire.',
            'adresse.string'           => 'L\'adresse doit être un texte.',
            'adresse.max'              => 'L\'adresse ne peut pas dépasser 500 caractères.',

            'type_cours.required'      => 'Le type de cours est obligatoire.',
            'type_cours.in'            => 'Le type de cours doit être "presentiel" ou "en_ligne".',

            'niveau.required'          => 'Le niveau est obligatoire.',
            'niveau.in'                => 'Le niveau doit être l\'un des suivants: A0, A1, A2, B1, B2, C1.',

            'horaire_prefere.string'   => 'L\'horaire préféré doit être un texte.',
            'horaire_prefere.max'      => 'L\'horaire préféré ne peut pas dépasser 50 caractères.',

            'date_start.required'      => 'La date de démarrage est obligatoire.',
            'date_start.date'          => 'La date de démarrage doit être une date valide.',

            'group_id.required'        => 'Le groupe est obligatoire.',
            'group_id.integer'         => 'Le groupe doit être un nombre entier.',
            'group_id.min'             => 'Le groupe doit être valide.',
            'group_id.max'             => 'Le groupe doit être valide.',

            'centre.required'          => 'Le centre est obligatoire pour les cours en présentiel.',
            'centre.integer'           => 'Le centre doit être un nombre entier.',
            'centre.exists'            => 'Le centre sélectionné n\'existe pas.',
        ];
    }

    /**
     * Get the data that was being validated.
     */
    protected function failedValidation($validator)
    {
        // This will automatically return JSON with 422 status
        // because we're using expectsJson() in the controller
        parent::failedValidation($validator);
    }
}
