<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'banners';

    protected $fillable = [
        'banner_image',
        'title',
        'subtitle',
        'smtag',
        'primary_btn',
        'primary_btn_url',
        'primary_btn_color',
        'primary_btn_text_color',
        'secondary_btn',
        'secondary_btn_url',
        'secondary_btn_color',
        'secondary_btn_text_color',
        'sort_order',
        'status',
    ];
}
