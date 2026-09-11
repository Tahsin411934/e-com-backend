<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sizeId = $this->route('size');

        return [
            'size_group_id' => 'required|integer|exists:size_groups,id',
            'group_name' => 'nullable|string|max:160', // denormalized, auto-synced
            'sizes' => 'required|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'size_group_id.required' => 'Group name is required.',
            'size_group_id.exists' => 'Selected size group does not exist.',
            'sizes.required' => 'Please enter at least one size.',
            'status.in' => 'Status is invalid.',
        ];
    }
}
