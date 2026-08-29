<?php

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->input('user_id');
        $uniqueEmail = 'unique:users,email'.($userId ? ','.$userId : '');
        $uniquePhone = 'unique:users,phone'.($userId ? ','.$userId : '');

        $rules = [
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|'.$uniqueEmail,
            'phone' => 'nullable|string|max:32|'.$uniquePhone,
            'status' => 'required|in:active,inactive,blocked,deleted',
            'role_id' => 'required|exists:roles,id',
        ];

        // Password is only required on create, optional on update
        if (! $userId) {
            $rules['password_hash'] = 'required|string|max:255';
        } else {
            $rules['password_hash'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'password_hash.required' => 'Password is required.',
            'status.in' => 'User status is invalid.',
        ];
    }
}
