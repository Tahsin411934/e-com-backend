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
            'group_name' => 'required|string|max:160',
            'sizes' => 'required|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'group_name.required' => 'Group name is required.',
            'sizes.required' => 'Please enter at least one size.',
            'status.in' => 'Status is invalid.',
        ];
    }
}
