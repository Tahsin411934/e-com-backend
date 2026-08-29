<?php

namespace Modules\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $registerId = $this->route('pos_register') ?? $this->input('register_id');

        return [
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:160',
            'code' => 'required|string|max:50|unique:pos_registers,code,'.$registerId,
            'type' => 'required|in:counter,mobile,kiosk',
            'status' => 'required|in:active,inactive,offline',
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'Store is required.',
            'store_id.exists' => 'Selected store does not exist.',
            'name.required' => 'Register name is required.',
            'code.required' => 'Register code is required.',
            'code.unique' => 'This register code is already in use.',
            'type.required' => 'Register type is required.',
            'type.in' => 'Register type is invalid.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status is invalid.',
        ];
    }
}
