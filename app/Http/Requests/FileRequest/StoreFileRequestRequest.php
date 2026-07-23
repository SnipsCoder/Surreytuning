<?php

namespace App\Http\Requests\FileRequest;

use App\Enums\TransmissionType;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreFileRequestRequest extends FormRequest
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
            'year' => ['required', 'integer', 'min:1990', 'max:2030'],
            'engine' => ['required', 'string', 'max:50'],
            'fuel' => ['required', 'string', Rule::in(Setting::fuelTypes())],
            'transmission' => ['required', new Enum(TransmissionType::class)],
            'file_stage_id' => ['required', 'exists:file_stages,id'],
            'tool_id' => ['required', 'exists:tuning_tools,id'],
            'file' => ['required', 'file', 'max:51200'],
            'registration' => ['nullable', 'string', 'max:20'],
            'vin_number' => ['nullable', 'string', 'max:50'],
            'engine_code' => ['nullable', 'string', 'max:50'],
            'bhp_before' => ['nullable', 'numeric', 'min:0'],
            'file_type' => ['required', 'in:ecu,tcu,adblue'],
            'torque_before_nm' => ['nullable', 'numeric', 'min:0'],
            'ecu_model_no' => ['nullable', 'string', 'max:100'],
            'client_notes' => ['nullable', 'string', 'max:2000'],
            'file_options' => ['nullable', 'array'],
            'file_options.*' => ['integer', 'exists:file_options,id'],
            'dtc_codes' => ['nullable', 'array'],
            'dtc_codes.*' => ['string', 'max:20'],
        ];
    }
}
