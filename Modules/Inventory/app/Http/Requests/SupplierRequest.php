<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierId = $this->input('supplier_id');

        return [
            'store_id' => 'nullable|exists:stores,id',
            'name' => 'required|string|max:220',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:32',
            'contact_person' => 'nullable|string|max:220',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:220',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }
}
