<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => 'nullable|integer|exists:stores,id',
            'name' => 'required|string|max:160',
            'slug' => 'required|string|max:180|unique:brands,slug',
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Brand name is required.',
            'slug.required' => 'Brand slug is required.',
            'slug.unique' => 'Brand slug must be unique.',
            'logo.image' => 'Brand logo must be a valid image.',
            'logo.max' => 'Brand logo may not be greater than 2 MB.',
            'status.in' => 'Brand status is invalid.',
        ];
    }
}
