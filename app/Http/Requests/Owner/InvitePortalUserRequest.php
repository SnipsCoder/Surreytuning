<?php

namespace App\Http\Requests\Owner;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class InvitePortalUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => [
                'required',
                new Enum(UserRole::class),
                Rule::in([UserRole::Owner->value, UserRole::Technician->value, UserRole::Tuner->value]),
            ],
        ];
    }
}
