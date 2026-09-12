<?php

namespace Modules\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'register_id' => 'required|exists:pos_registers,id',
            'user_id' => 'required|exists:users,id',
            'opened_at' => 'nullable|date',
            'opening_balance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'register_id.required' => 'Register is required.',
            'register_id.exists' => 'Selected register does not exist.',
            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user does not exist.',
        ];
    }
}
