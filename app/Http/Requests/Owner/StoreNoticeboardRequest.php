<?php

namespace App\Http\Requests\Owner;

use App\Enums\NoticePriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoticeboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(array_column(NoticePriority::cases(), 'value'))],
            'show_from' => ['nullable', 'date'],
            'show_until' => ['nullable', 'date', 'after_or_equal:show_from'],
            'is_active' => ['boolean'],
        ];
    }
}
