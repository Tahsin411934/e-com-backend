<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locationId = $this->route('inventory_location') ?? $this->input('location_id');

        return [
            'store_id' => 'nullable|exists:stores,id',
            'name' => 'required|string|max:160',
            'location_type' => 'required|in:warehouse,retail,delivery_hub',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'Store is required.',
            'store_id.exists' => 'Selected store does not exist.',
            'name.required' => 'Location name is required.',
            'location_type.required' => 'Location type is required.',
            'location_type.in' => 'Location type is invalid.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status is invalid.',
        ];
    }
}
