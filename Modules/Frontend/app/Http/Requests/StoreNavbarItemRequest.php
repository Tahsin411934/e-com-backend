<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNavbarItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:160',
            'slug' => 'required|string|max:180|unique:navbar_items,slug',
            'url' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Navbar item name is required.',
            'slug.required' => 'Slug is required.',
            'slug.unique' => 'This slug is already taken.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }
}
