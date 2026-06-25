<?php

namespace App\Http\Requests\Owner;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFileStageRequest extends FormRequest
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
            'vehicle_type' => ['required', Rule::in(array_column(VehicleType::cases(), 'value'))],
            'price_net' => ['required', 'numeric', 'min:0'],
            'vat_applicable' => ['boolean'],
            'turnaround_hours' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ];
    }
}
