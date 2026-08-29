<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => 'required|exists:inventory_locations,id',
            'variant_id' => 'required|exists:product_variants,id',
            'movement_type' => 'required|in:purchase,sale,return,adjustment,transfer_in,transfer_out,reservation,release',
            'quantity' => 'required|integer',
            'reference_type' => 'nullable|string|max:60',
            'reference_id' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required' => 'Location is required.',
            'location_id.exists' => 'Selected location does not exist.',
            'variant_id.required' => 'Product variant is required.',
            'variant_id.exists' => 'Selected variant does not exist.',
            'movement_type.required' => 'Movement type is required.',
            'movement_type.in' => 'Movement type is invalid.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be an integer.',
        ];
    }
}
