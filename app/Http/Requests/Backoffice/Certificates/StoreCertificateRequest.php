<?php

namespace App\Http\Requests\Backoffice\Certificates;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('certificate_type', 'b2');

        $rules = [
            'last_name'          => 'required|string|max:255',
            'first_name'         => 'required|string|max:255',
            'birth_date'         => 'required|date',
            'birth_place'        => 'nullable|string|max:255',

            'certificate_type'   => 'required|in:a1,a2,b1,b2',
            'site_id'            => 'required|exists:sites,id',
            'exam_level'         => 'required|string|max:255',
            'exam_date'          => 'required|date',
            'issue_date'         => 'required|date',
            'certificate_number' => 'nullable|string|max:255|unique:certificates,certificate_number',

            'final_result'       => 'required|string|max:255',
            'ergebnis_note'      => 'nullable|string|max:255',
        ];

        if ($type === 'a1') {
            $rules['reading_score']   = 'required|integer|min:0|max:15';
            $rules['listening_score'] = 'required|integer|min:0|max:15';
            $rules['writing_score']   = 'required|integer|min:0|max:15';
            $rules['speaking_score']  = 'required|integer|min:0|max:15';
        } elseif ($type === 'a2') {
            $rules['reading_score']   = 'required|integer|min:0|max:25';
            $rules['listening_score'] = 'required|integer|min:0|max:25';
            $rules['writing_score']   = 'required|integer|min:0|max:25';
            $rules['speaking_score']  = 'required|integer|min:0|max:25';
        } elseif ($type === 'b1') {
            $rules['reading_score']   = 'required|integer|min:0|max:60';
            $rules['listening_score'] = 'required|integer|min:0|max:60';
            $rules['writing_score']   = 'required|integer|min:0|max:60';
            $rules['speaking_score']  = 'required|integer|min:0|max:60';
        } else {
            $rules['reading_score']        = 'required|integer|min:0|max:75';
            $rules['grammar_score']        = 'required|integer|min:0|max:30';
            $rules['listening_score']      = 'required|integer|min:0|max:75';
            $rules['writing_score']        = 'required|integer|min:0|max:45';
            $rules['presentation_score']   = 'required|integer|min:0|max:25';
            $rules['discussion_score']     = 'required|integer|min:0|max:25';
            $rules['problemsolving_score'] = 'required|integer|min:0|max:25';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'certificate_number.unique' => 'Ce numéro de certificat existe déjà.',
            'last_name.required'        => 'Le nom de famille est obligatoire.',
            'first_name.required'       => 'Le prénom est obligatoire.',
        ];
    }
}
