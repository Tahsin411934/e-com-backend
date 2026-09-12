<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_email' => ['required', 'email', 'max:160'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'product_name' => ['required', 'string', 'max:220'],
            'product_description' => ['nullable', 'string', 'max:2000'],
            'product_image' => ['nullable', 'image', 'max:5120'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'expected_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'in:pending,approved,rejected,fulfilled'],
        ];
    }
}
