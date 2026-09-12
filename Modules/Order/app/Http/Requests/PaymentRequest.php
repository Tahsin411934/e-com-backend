<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'provider' => ['required', 'string', 'max:80'],
            'provider_payment_id' => ['nullable', 'string', 'max:180'],
            'method' => ['required', 'in:card,cash,bank_transfer,wallet,cod,gift_card,other'],
            'status' => ['required', 'in:pending,authorized,captured,failed,cancelled,refunded'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'paid_at' => ['nullable', 'date'],
            'raw_response' => ['nullable', 'array'],
        ];
    }
}
