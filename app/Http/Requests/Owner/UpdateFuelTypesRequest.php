<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFuelTypesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Drop empty rows and de-duplicate before validation so a blank repeater
     * field or a stray duplicate never blocks the save.
     */
    protected function prepareForValidation(): void
    {
        $types = collect($this->input('fuel_types', []))
            ->map(fn ($t) => is_string($t) ? trim($t) : '')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->merge(['fuel_types' => $types]);
    }

    public function rules(): array
    {
        return [
            'fuel_types' => ['required', 'array', 'min:1'],
            'fuel_types.*' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'fuel_types.required' => 'Add at least one fuel type.',
            'fuel_types.min' => 'Add at least one fuel type.',
        ];
    }
}
