<?php

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission') ?? $this->input('permission_id');
        $uniqueName = 'unique:permissions,name'.($permissionId ? ','.$permissionId : '');

        return [
            'name' => 'required|string|max:120|'.$uniqueName,
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is required.',
            'name.unique' => 'Permission name must be unique.',
        ];
    }
}
