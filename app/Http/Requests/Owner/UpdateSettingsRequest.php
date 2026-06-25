<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_address' => ['nullable', 'string'],
            'returns_address' => ['nullable', 'string'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'company_number' => ['nullable', 'string', 'max:255'],
            'invoice_start_number' => ['nullable', 'integer', 'min:1'],
            'invoice_reference_prefix' => ['nullable', 'string', 'max:20'],
            'bcc_invoice_email' => ['nullable', 'email', 'max:255'],
            'dealer_auto_onboard' => ['boolean'],
            'terms_and_conditions' => ['nullable', 'string'],
        ];
    }
}
