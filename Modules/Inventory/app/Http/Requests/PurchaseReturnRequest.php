<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $returnId = $this->route('purchase_return');

        return [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'store_id' => 'required|exists:stores,id',
            'status' => 'required|in:draft,returned,partially_refunded,refunded,cancelled',
            'refund_status' => 'required|in:pending,partial,full',
            'total_refund_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
            'return_date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_order_id.required' => 'Purchase order is required.',
            'supplier_id.required' => 'Supplier is required.',
            'store_id.required' => 'Store is required.',
            'return_date.required' => 'Return date is required.',
        ];
    }
}
