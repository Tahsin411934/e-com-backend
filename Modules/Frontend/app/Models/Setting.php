<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use CustomSoftDeletes;

    protected $table = 'settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}