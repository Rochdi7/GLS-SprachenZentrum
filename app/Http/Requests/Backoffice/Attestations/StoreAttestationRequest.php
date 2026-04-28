<?php

namespace App\Http\Requests\Backoffice\Attestations;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_name'          => 'required|string|max:255',
            'first_name'         => 'required|string|max:255',
            'birth_date'         => 'nullable|date',
            'birth_place'        => 'nullable|string|max:255',

            'group_id'           => 'required|exists:groups,id',
            'level'              => 'required|in:A1,A2,B1,B2,C1',

            'course_start_date'  => 'nullable|date',
            'course_end_date'    => 'nullable|date',

            'niveau_start_date'  => 'nullable|date',
            'niveau_end_date'    => 'nullable|date',

            'fees_status'        => 'required|in:full,partial',

            'stufe_index'        => 'required|integer|min:1|max:9',
            'stufe_total'        => 'required|integer|min:1|max:9',
            'erfolg'             => 'required|string|max:50',
            'language'           => 'required|in:de_fr,de,fr,en',

            'city'               => 'nullable|string|max:120',
            'issue_date'         => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required'   => 'Le nom est obligatoire.',
            'first_name.required'  => 'Le prénom est obligatoire.',
            'group_id.required'    => 'Veuillez sélectionner un groupe.',
            'level.required'       => 'Veuillez sélectionner un niveau.',
        ];
    }
}
