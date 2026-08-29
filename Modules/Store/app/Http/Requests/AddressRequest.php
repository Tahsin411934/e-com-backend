<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'store_id' => 'nullable|exists:stores,id',
            'label' => 'nullable|string|max:80',
            'contact_name' => 'nullable|string|max:160',
            'contact_phone' => 'nullable|string|max:32',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:120',
            'state' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:32',
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'address_line1.required' => 'Address line 1 is required.',
            'city.required' => 'City is required.',
            'country_id.required' => 'Country is required.',
            'country_id.exists' => 'Selected country does not exist.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }
}
