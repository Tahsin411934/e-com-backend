<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staffId = $this->route('store_staff') ?? $this->input('staff_id');
        $uniqueCode = 'unique:store_staff,staff_code'.($staffId ? ','.$staffId : '');

        return [
            'store_id' => 'required|exists:stores,id',
            'user_id' => 'nullable|exists:users,id',
            'first_name' => 'required_without:user_id|string|max:100',
            'last_name' => 'required_without:user_id|string|max:100',
            'email' => 'required_without:user_id|email|max:255|unique:users,email',
            'password' => 'required_without:user_id|string|min:8',
            'staff_code' => 'nullable|string|max:40|'.$uniqueCode,
            'status' => 'required|in:active,inactive,terminated',
            'hired_at' => 'nullable|date',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'integer|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'Store is required.',
            'store_id.exists' => 'Selected store does not exist.',
            'user_id.required' => 'User is required.',
            'user_id.unique' => 'This user is already assigned to a store.',
            'staff_code.unique' => 'Staff code must be unique within the store.',
            'status.in' => 'Staff status is invalid.',
        ];
    }
}
