<?php

namespace App\Http\Requests\Backoffice\Payroll;

use App\Models\PresenceImport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the payment info required to move an import to "paid".
 */
class MarkPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_date'      => ['required', 'date'],
            'payment_method'    => ['required', 'string', Rule::in(PresenceImport::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'payment_notes'     => ['nullable', 'string', 'max:1000'],
            'comment'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_date.required'   => 'La date de paiement est requise.',
            'payment_method.required' => 'Le mode de paiement est requis.',
            'payment_method.in'       => 'Le mode de paiement sélectionné est invalide.',
        ];
    }
}
