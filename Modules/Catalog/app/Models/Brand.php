<?php

namespace Modules\Catalog\Models;

use App\Traits\BelongsToStore;
use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use BelongsToStore;
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = ['store_id', 'name', 'slug', 'logo_url', 'status'];

    public function store()
    {
        return $this->belongsTo(\Modules\Store\Models\Store::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
