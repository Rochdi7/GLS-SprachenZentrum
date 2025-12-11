<?php

namespace App\Http\Requests\Backoffice\Certificates;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // Personal
            'last_name'            => 'required|string|max:255',
            'first_name'           => 'required|string|max:255',
            'birth_date'           => 'required|date',
            'birth_place'          => 'nullable|string|max:255',

            // Exam
            'exam_level'           => 'required|string|max:255',
            'exam_date'            => 'required|date',
            'issue_date'           => 'required|date',

            'certificate_number'   =>
                'required|string|max:255|unique:certificates,certificate_number,' . $this->id,

            // Written
            'reading_score'        => 'required|integer|min:0',
            'grammar_score'        => 'required|integer|min:0',
            'listening_score'      => 'required|integer|min:0',
            'writing_score'        => 'required|integer|min:0',

            // Oral
            'presentation_score'   => 'required|integer|min:0',
            'discussion_score'     => 'required|integer|min:0',
            'problemsolving_score' => 'required|integer|min:0',

            // Final
            'final_result'         => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'certificate_number.unique' => 'Ce numéro de certificat existe déjà.',
        ];
    }
}
