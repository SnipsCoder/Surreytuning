<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreWinolsBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:1'],
            'price_net' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
