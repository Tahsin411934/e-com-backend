<?php

namespace Modules\Reviews\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'is_verified_purchase' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('user_id') && auth()->check()) {
            $this->merge(['user_id' => auth()->id()]);
        }
    }
}
