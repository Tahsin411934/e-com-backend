<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Size extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'sizes';

    protected $fillable = [
        'group_name',
        'sizes',
        'status',
    ];
}