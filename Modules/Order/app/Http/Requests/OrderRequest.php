<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orderId = $this->route('order');

        return [
            'order_number' => ['nullable', 'string', 'max:100', Rule::unique('orders', 'order_number')->ignore($orderId)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'source' => ['required', 'in:web,mobile,pos,admin,marketplace'],
            'status' => ['required', 'in:pending,confirmed,processing,ready,completed,cancelled,refunded'],
            'payment_status' => ['required', 'in:unpaid,authorized,paid,partially_refunded,refunded,failed'],
            'fulfillment_status' => ['required', 'in:unfulfilled,partial,fulfilled,returned'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
            'shipping_total' => ['nullable', 'numeric', 'min:0'],
            'grand_total' => ['nullable', 'numeric', 'min:0'],
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'billing_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'shipping_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'placed_at' => ['nullable', 'date'],
            'cancelled_at' => ['nullable', 'date'],
        ];
    }
}
