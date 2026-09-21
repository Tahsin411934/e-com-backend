<?php

namespace Modules\Store\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Identity\Models\User;
use Modules\Store\Models\Plan;

class StoreOwnerRegistrationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Keep older clients compatible while the plan selector is rolled out.
        $this->merge(['plan_slug' => $this->input('plan_slug', 'free-trial')]);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(ApiResponse::validationError($validator->errors()));
    }

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
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s()\-]{7,20}$/', Rule::unique(User::class, 'phone')],
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
                Rule::unique('stores', 'name'),
            ],
            'store_slug' => [
                'required', 'string', 'min:2', 'max:180', 'alpha_dash',
                Rule::notIn(config('storefront.reserved_subdomains', [])),
                'unique:stores,slug',
            ],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'timezone' => ['nullable', 'timezone'],
            'plan_slug' => ['required', Rule::exists('plans', 'slug')->where(fn ($query) => $query->where('is_active', true)->where('is_public', true))],
        ];
    }

    public function messages(): array
    {
        return [
            'store_name.required' => 'Store / company name is required.',
            'phone.required' => 'A phone number is required for your store account.',
            'phone.regex' => 'Enter a valid phone number using digits, spaces, +, hyphens or parentheses.',
            'store_slug.required' => 'Choose a free store URL for your storefront.',
            'store_slug.min' => 'Your store URL must be at least 2 characters.',
            'store_name.min' => 'Store name must be at least 2 characters.',
            'store_name.max' => 'Store name may not be longer than 160 characters.',
            'store_name.regex' => 'Store name must look professional: letters, numbers, spaces and characters like & . , ( ) \' - / + only.',
            'store_name.unique' => 'This store name is already registered. Please choose another name.',
            'store_slug.unique' => 'This store URL slug is already taken.',
            'store_slug.not_in' => 'This store URL is reserved for the platform. Please choose another name.',
            'currency_code.size' => 'Currency code must be exactly 3 characters (e.g. USD, BDT).',
            'timezone.timezone' => 'Please provide a valid timezone (e.g. Asia/Dhaka).',
            'plan_slug.required' => 'Please select a subscription plan.',
            'plan_slug.exists' => 'The selected subscription plan is unavailable.',
        ];
    }
}
