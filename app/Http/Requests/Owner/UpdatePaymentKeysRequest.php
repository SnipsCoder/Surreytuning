<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentKeysRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Public identifiers — editable directly.
            'stripe_public_key' => ['nullable', 'string', 'max:255'],
            'evc_account_number' => ['nullable', 'string', 'max:255'],
            'paypal_client_id' => ['nullable', 'string', 'max:255'],
            'whatsapp_phone_number_id' => ['nullable', 'string', 'max:255'],
            'whatsapp_template_name' => ['nullable', 'string', 'max:255'],
            'whatsapp_template_language' => ['nullable', 'string', 'max:10'],

            // Secrets — blank means "leave unchanged"; a value replaces it.
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            'evc_password' => ['nullable', 'string', 'max:255'],
            'paypal_secret' => ['nullable', 'string', 'max:255'],
            'whatsapp_access_token' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
