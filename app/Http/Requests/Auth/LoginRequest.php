<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],
            'password' => ['nullable', 'string'],
            'device_id' => ['nullable', 'string', 'max:120'],
        ];
    }
}
