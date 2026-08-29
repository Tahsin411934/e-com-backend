<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $countryId = $this->route('country') ?? $this->input('country_id');

        return [
            'iso2' => 'required|string|size:2|unique:countries,iso2'.($countryId ? ','.$countryId : ''),
            'name' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'iso2.required' => 'ISO2 code is required.',
            'iso2.size' => 'ISO2 must be exactly 2 characters.',
            'iso2.unique' => 'ISO2 code must be unique.',
            'name.required' => 'Country name is required.',
        ];
    }
}
