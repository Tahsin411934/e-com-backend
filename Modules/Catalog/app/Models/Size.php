<?php

namespace Modules\Catalog\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'sizes';

    protected $fillable = [
        'group_name',
        'sizes',
        'status',
    ];
}
