<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Brand extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'brands';

    protected $fillable = ['name', 'slug', 'logo_url', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
