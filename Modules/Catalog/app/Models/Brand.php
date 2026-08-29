<?php

namespace Modules\Catalog\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = ['name', 'slug', 'logo_url', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
