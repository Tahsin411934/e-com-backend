<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'smtag' => 'nullable|string|max:255',
            'primary_btn' => 'nullable|string|max:255',
            'primary_btn_url' => 'nullable|string|max:500',
            'primary_btn_color' => 'nullable|string|max:50',
            'primary_btn_text_color' => 'nullable|string|max:50',
            'secondary_btn' => 'nullable|string|max:255',
            'secondary_btn_url' => 'nullable|string|max:500',
            'secondary_btn_color' => 'nullable|string|max:50',
            'secondary_btn_text_color' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Banner title is required.',
            'banner_image.image' => 'The file must be an image.',
            'banner_image.mimes' => 'Supported formats: jpeg, png, jpg, gif, svg, webp.',
            'banner_image.max' => 'Image size must not exceed 2MB.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }
}
