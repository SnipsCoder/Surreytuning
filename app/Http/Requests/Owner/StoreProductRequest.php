<?php

namespace App\Http\Requests\Owner;

use App\Enums\ProductPaymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_net' => ['required', 'numeric', 'min:0'],
            'vat_applicable' => ['boolean'],
            'payment_type' => ['required', Rule::in(array_column(ProductPaymentType::cases(), 'value'))],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ];
    }
}
