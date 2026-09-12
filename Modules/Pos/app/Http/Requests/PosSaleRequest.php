<?php

namespace Modules\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'register_id' => 'required|exists:pos_registers,id',
            'shift_id' => 'required|exists:pos_shifts,id',
            'user_id' => 'required|exists:users,id',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
            'card_amount' => 'nullable|numeric|min:0',
            'other_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:paid,partial,pending,refunded',
            'status' => 'nullable|in:completed,voided,refunded',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'register_id.required' => 'Register is required.',
            'register_id.exists' => 'Selected register does not exist.',
            'shift_id.required' => 'Shift is required.',
            'shift_id.exists' => 'Selected shift does not exist.',
            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user does not exist.',
        ];
    }
}
