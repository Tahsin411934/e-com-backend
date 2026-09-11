<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSizeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Resource route binds {size_group}; fall back to {id} defensively so
        // the unique-name rule ignores the CURRENT row on update.
        $sizeGroupId = $this->route('size_group') ?? $this->route('size-group') ?? $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:160',
                Rule::unique('size_groups', 'name')->ignore($sizeGroupId),
            ],
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Size group name is required.',
            'name.unique' => 'This size group name already exists.',
            'status.in' => 'Status is invalid.',
        ];
    }
}
