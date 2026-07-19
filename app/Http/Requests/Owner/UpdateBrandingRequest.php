<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo_light' => ['nullable', 'image', 'max:2048'],
            'logo_dark' => ['nullable', 'image', 'max:2048'],
            'login_background' => ['nullable', 'image', 'max:4096'],
            'portal_logo' => ['nullable', 'image', 'max:2048'],
            'invoice_header' => ['nullable', 'image', 'max:2048'],
            'theme_colour' => ['nullable', 'string', 'max:7'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
