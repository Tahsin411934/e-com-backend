<?php

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('user_id') && auth()->check()) {
            $this->merge(['user_id' => auth()->id()]);
        }
    }
}
