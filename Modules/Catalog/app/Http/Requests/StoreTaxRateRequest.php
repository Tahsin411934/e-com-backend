<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'applies_to' => 'required|in:all,products,services,digital',
            'status' => 'required|in:active,inactive',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ];
    }
}
