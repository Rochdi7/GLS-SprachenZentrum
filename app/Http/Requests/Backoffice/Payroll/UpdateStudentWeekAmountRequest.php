<?php

namespace App\Http\Requests\Backoffice\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentWeekAmountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week' => ['required', 'integer', 'between:1,4'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
        ];
    }
}
