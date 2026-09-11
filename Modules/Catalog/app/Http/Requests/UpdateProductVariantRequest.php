<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variantId = $this->route('variant') ?? $this->route('id');

        return [
            'product_id' => 'required|integer|exists:products,id',
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_variants', 'sku')->ignore($variantId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('product_variants', 'barcode')->ignore($variantId),
            ],
            'name' => 'required|string|max:220',
            'attributes' => 'nullable|array',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'weight_grams' => 'nullable|integer|min:0',
            'length_mm' => 'nullable|integer|min:0',
            'width_mm' => 'nullable|integer|min:0',
            'height_mm' => 'nullable|integer|min:0',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'The product is required.',
            'sku.required' => 'SKU is required.',
            'sale_price.required' => 'Sale price is required.',
            'status.in' => 'Variant status is invalid.',
        ];
    }
}
