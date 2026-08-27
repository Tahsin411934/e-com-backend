<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Unit extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

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