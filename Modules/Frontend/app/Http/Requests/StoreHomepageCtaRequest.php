<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomepageCtaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cta_style'                => 'nullable|string|in:style1,style2,style3,style4,style5',
            'title'                    => 'nullable|string|max:255',
            'subtitle'                 => 'nullable|string|max:500',
            'description'              => 'nullable|string',
            'image'                    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'banner_image'             => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'button_text'              => 'nullable|string|max:255',
            'button_link'              => 'nullable|string|max:500',
            'background_color'         => 'nullable|string|max:20',
            'text_color'               => 'nullable|string|max:20',
            'button_color'             => 'nullable|string|max:20',
            'button_text_color'        => 'nullable|string|max:20',
            'overlay_color'            => 'nullable|string|max:20',
            'badge_text'               => 'nullable|string|max:100',
            'badge_color'              => 'nullable|string|max:20',
            'secondary_button_text'    => 'nullable|string|max:255',
            'secondary_button_link'    => 'nullable|string|max:500',
            'secondary_button_color'   => 'nullable|string|max:20',
            'secondary_button_text_color' => 'nullable|string|max:20',
            'feature_icon_1'           => 'nullable|string|max:100',
            'feature_text_1'           => 'nullable|string|max:255',
            'feature_icon_2'           => 'nullable|string|max:100',
            'feature_text_2'           => 'nullable|string|max:255',
            'feature_icon_3'           => 'nullable|string|max:100',
            'feature_text_3'           => 'nullable|string|max:255',
            // Dynamic positioning & margin
            'button_position'             => 'nullable|string|max:50',
            'button_margin_top'           => 'nullable|integer',
            'button_margin_bottom'        => 'nullable|integer',
            'button_margin_left'          => 'nullable|integer',
            'button_margin_right'         => 'nullable|integer',
            'secondary_button_position'   => 'nullable|string|max:50',
            'secondary_button_margin_top' => 'nullable|integer',
            'secondary_button_margin_bottom' => 'nullable|integer',
            'secondary_button_margin_left'   => 'nullable|integer',
            'secondary_button_margin_right'  => 'nullable|integer',
            'content_alignment'           => 'nullable|string|max:50',
            'content_margin_top'          => 'nullable|integer',
            'content_margin_bottom'       => 'nullable|integer',
            'content_margin_left'         => 'nullable|integer',
            'content_margin_right'        => 'nullable|integer',
            'sort_order'               => 'nullable|integer|min:0',
            'status'                   => 'nullable|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'image.image'          => 'The file must be an image.',
            'image.mimes'          => 'Supported formats: jpeg, png, jpg, gif, svg, webp.',
            'image.max'            => 'Image size must not exceed 5MB.',
            'banner_image.image'   => 'The banner file must be an image.',
            'banner_image.mimes'   => 'Supported formats: jpeg, png, jpg, gif, svg, webp.',
            'banner_image.max'     => 'Banner image size must not exceed 10MB.',
            'cta_style.in'         => 'Invalid CTA style selected.',
            'status.in'            => 'Status must be active or inactive.',
        ];
    }
}