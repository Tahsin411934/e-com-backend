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
        $isUpdate = filled($storeId);
        $uniqueSlug = 'unique:stores,slug'.($storeId ? ','.$storeId : '');

        // A new store always creates its owner's login credentials, so the
        // owner email must be a free, valid login email. On update the
        // credentials are left untouched.
        $rules = [
            'name' => 'required|string|max:160',
            'slug' => 'required|string|max:180|'.$uniqueSlug,
            'email' => $isUpdate
                ? 'nullable|email|max:255'
                : 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:32',
            'status' => 'required|in:active,inactive,maintenance',
            'currency_code' => 'required|string|size:3',
            'timezone' => 'required|string|max:64',
        ];

        if (! $isUpdate) {
            $rules['owner_first_name'] = 'required|string|max:255';
            $rules['owner_last_name'] = 'required|string|max:255';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Store name is required.',
            'slug.required' => 'Store slug is required.',
            'slug.unique' => 'Store slug must be unique.',
            'status.in' => 'Store status is invalid.',
            'currency_code.size' => 'Currency code must be exactly 3 characters.',
            'email.required' => 'Owner email is required - it is used as the login.',
            'email.unique' => 'This email is already registered to a user.',
            'owner_first_name.required' => 'Owner first name is required.',
            'owner_last_name.required' => 'Owner last name is required.',
            'password.required' => 'Owner password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
