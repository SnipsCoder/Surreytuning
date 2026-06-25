<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleStatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year_from' => ['required', 'integer', 'min:1900', 'max:2100'],
            'year_to' => ['required', 'integer', 'min:1900', 'max:2100', 'gte:year_from'],
            'engine' => ['required', 'string', 'max:50'],
            'fuel' => ['required', 'in:petrol,diesel,electric,hybrid'],
            'bhp_before' => ['required', 'numeric', 'min:0'],
            'bhp_after' => ['required', 'numeric', 'min:0'],
            'torque_before_nm' => ['required', 'numeric', 'min:0'],
            'torque_after_nm' => ['required', 'numeric', 'min:0'],
            'stage' => ['required', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
