<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementBarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'left_text' => 'nullable|string|max:500',
            'center_text' => 'nullable|string|max:500',
            'right_text' => 'nullable|string|max:500',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'store_id' => 'nullable|integer|exists:stores,id',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be active or inactive.',
            'store_id.exists' => 'The selected store does not exist.',
        ];
    }
}
