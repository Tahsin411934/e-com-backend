<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Identity\Models\User;

class StoreOwnerRegistrationRequest extends FormRequest
{
    /**
     * Public SaaS registration endpoint - always authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules for registering a store owner (tenant) together with their store.
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique(User::class, 'phone')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Professional store name: must start and end with a letter or number
            // and may only contain letters, numbers, spaces and common business
            // punctuation (& . , ( ) ' - / +).
            'store_name' => [
                'required',
                'string',
                'min:2',
                'max:160',
                'regex:/^[\pL\pN][\pL\pN .,&()\'\-\/+]{0,158}[\pL\pN.)]$/u',
            ],
            'store_slug' => ['nullable', 'string', 'max:180', 'alpha_dash', 'unique:stores,slug'],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'timezone' => ['nullable', 'timezone'],
        ];
    }

    public function messages(): array
    {
        return [
            'store_name.required' => 'Store / company name is required.',
            'store_name.min' => 'Store name must be at least 2 characters.',
            'store_name.max' => 'Store name may not be longer than 160 characters.',
            'store_name.regex' => 'Store name must look professional: letters, numbers, spaces and characters like & . , ( ) \' - / + only.',
            'store_slug.unique' => 'This store URL slug is already taken.',
            'currency_code.size' => 'Currency code must be exactly 3 characters (e.g. USD, BDT).',
            'timezone.timezone' => 'Please provide a valid timezone (e.g. Asia/Dhaka).',
        ];
    }
}
