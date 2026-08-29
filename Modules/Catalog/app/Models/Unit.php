<?php

namespace Modules\Catalog\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'type',
        'status',
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
    ];
}
