<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalog\Models\Brand;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand') ?? $this->route('id');
        $brand = Brand::findOrFail($brandId);

        return [
            'store_id' => 'nullable|integer|exists:stores,id',
            'name' => 'required|string|max:160',
            'slug' => [
                'required',
                'string',
                'max:180',
                Rule::unique('brands', 'slug')->where('store_id', $brand->store_id)->ignore($brand),
            ],
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Brand name is required.',
            'slug.required' => 'Brand slug is required.',
            'slug.unique' => 'Brand slug must be unique within this store.',
            'logo.image' => 'Brand logo must be a valid image.',
            'logo.max' => 'Brand logo may not be greater than 2 MB.',
            'status.in' => 'Brand status is invalid.',
        ];
    }
}
