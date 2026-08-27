<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Banner extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

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