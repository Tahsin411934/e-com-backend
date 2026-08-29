<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = $this->route('store') ?? $this->input('store_id');
        $uniqueSlug = 'unique:stores,slug'.($storeId ? ','.$storeId : '');

        return [
            'name' => 'required|string|max:160',
            'slug' => 'required|string|max:180|'.$uniqueSlug,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:32',
            'status' => 'required|in:active,inactive,maintenance',
            'currency_code' => 'required|string|size:3',
            'timezone' => 'required|string|max:64',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Store name is required.',
            'slug.required' => 'Store slug is required.',
            'slug.unique' => 'Store slug must be unique.',
            'status.in' => 'Store status is invalid.',
            'currency_code.size' => 'Currency code must be exactly 3 characters.',
        ];
    }
}
