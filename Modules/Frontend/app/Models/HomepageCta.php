<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomepageCta extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'homepage_ctas';

    protected $fillable = [
        'cta_style',
        'title',
        'subtitle',
        'description',
        'image',
        'banner_image',
        'button_text',
        'button_link',
        'background_color',
        'text_color',
        'button_color',
        'button_text_color',
        'overlay_color',
        'badge_text',
        'badge_color',
        'secondary_button_text',
        'secondary_button_link',
        'secondary_button_color',
        'secondary_button_text_color',
        'feature_icon_1',
        'feature_text_1',
        'feature_icon_2',
        'feature_text_2',
        'feature_icon_3',
        'feature_text_3',
        'button_position',
        'button_margin_top',
        'button_margin_bottom',
        'button_margin_left',
        'button_margin_right',
        'secondary_button_position',
        'secondary_button_margin_top',
        'secondary_button_margin_bottom',
        'secondary_button_margin_left',
        'secondary_button_margin_right',
        'content_alignment',
        'content_margin_top',
        'content_margin_bottom',
        'content_margin_left',
        'content_margin_right',
        'sort_order',
        'status',
    ];
}