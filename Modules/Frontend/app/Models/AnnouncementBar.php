<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementBar extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'announcement_bars';

    protected $fillable = [
        'left_text',
        'center_text',
        'right_text',
        'background_color',
        'text_color',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
