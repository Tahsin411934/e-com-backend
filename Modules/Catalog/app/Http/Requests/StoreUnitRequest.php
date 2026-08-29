<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'slug' => 'required|string|max:140|unique:units,slug',
            'short_name' => 'required|string|max:30',
            'type' => 'required|in:quantity,weight,volume,length,area,time',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Unit name is required.',
            'slug.required' => 'Unit slug is required.',
            'slug.unique' => 'Unit slug must be unique.',
            'short_name.required' => 'Short name is required.',
            'type.required' => 'Unit type is required.',
            'type.in' => 'Unit type is invalid.',
            'status.in' => 'Unit status is invalid.',
        ];
    }
}
