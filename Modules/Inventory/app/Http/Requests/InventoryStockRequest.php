<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stockId = $this->route('inventory_stock') ?? $this->input('stock_id');

        return [
            'location_id' => 'required|exists:inventory_locations,id',
            'variant_id' => 'required|exists:product_variants,id',
            'variant_option_id' => 'nullable|exists:variant_options,id',
            'quantity_on_hand' => 'required|integer|min:0',
            'quantity_reserved' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required' => 'Location is required.',
            'location_id.exists' => 'Selected location does not exist.',
            'variant_id.required' => 'Product variant is required.',
            'variant_id.exists' => 'Selected variant does not exist.',
            'quantity_on_hand.required' => 'Quantity on hand is required.',
            'quantity_on_hand.min' => 'Quantity cannot be negative.',
            'quantity_reserved.min' => 'Reserved quantity cannot be negative.',
            'reorder_point.min' => 'Reorder point cannot be negative.',
        ];
    }
}