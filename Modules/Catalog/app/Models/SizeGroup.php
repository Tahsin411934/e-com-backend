<?php

namespace Modules\Catalog\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizeGroup extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'size_groups';

    protected $fillable = [
        'name',
        'status',
    ];

    public function sizes()
    {
        return $this->hasMany(Size::class, 'size_group_id');
    }
}
