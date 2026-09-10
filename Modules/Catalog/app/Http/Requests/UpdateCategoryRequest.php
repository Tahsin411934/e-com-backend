<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category');

        return [
            'store_id' => 'nullable|integer|exists:stores,id',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'name' => 'required|string|max:160',
            'slug' => [
                'required',
                'string',
                'max:180',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'image_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'slug.required' => 'Category slug is required.',
            'slug.unique' => 'Category slug must be unique.',
            'status.in' => 'Category status is invalid.',
        ];
    }
}
