<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^(\+62|0)8[1-9][0-9\s-]{6,14}$/', 'unique:users,phone'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'grade' => ['nullable', 'string', 'in:X,XI,XII'],
            'class_name' => ['nullable', 'string', 'max:50'],
            'device_id' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Format nomor HP tidak valid (contoh: 081234567890).',
            'phone.unique' => 'Nomor HP sudah terdaftar.',
        ];
    }
}
