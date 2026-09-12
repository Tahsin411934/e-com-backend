<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,processed,failed'],
            'processed_at' => ['nullable', 'date'],
        ];
    }
}
