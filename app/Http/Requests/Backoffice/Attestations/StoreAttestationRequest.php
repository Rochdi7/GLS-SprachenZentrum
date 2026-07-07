<?php

namespace App\Http\Requests\Backoffice\Attestations;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_legacy' => $this->boolean('is_legacy'),
        ]);
    }

    public function rules(): array
    {
        return [
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',

            'is_legacy' => 'nullable|boolean',
            'group_id' => 'nullable|required_if:is_legacy,false|exists:groups,id',
            'site_id' => 'nullable|required_if:is_legacy,true|exists:sites,id',
            'level' => 'required|in:A1,A2,B1,B2,C1',
            'level_from' => 'nullable|in:A1,A2,B1,B2,C1',

            'course_start_date' => 'nullable|date',
            'course_end_date' => 'nullable|date',

            'niveau_start_date' => 'nullable|date',
            'niveau_end_date' => 'nullable|date',
            'is_ongoing' => 'nullable|boolean',

            'units_45min' => 'required|integer|min:0',

            'fees_status' => 'required|in:full,partial',

            'stufe_index' => 'required|integer|min:1|max:9',
            'stufe_total' => 'required|integer|min:1|max:9',
            'methodology_text' => 'nullable|string|max:5000',
            'language' => 'required|in:de_fr',

            'city' => 'nullable|string|max:120',
            'issue_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required' => 'Le nom est obligatoire.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'group_id.required_if' => 'Veuillez sélectionner un groupe (ou cocher « Étudiant ancien »).',
            'site_id.required_if' => 'Veuillez sélectionner le centre (obligatoire pour un étudiant ancien).',
            'level.required' => 'Veuillez sélectionner un niveau.',
        ];
    }
}
