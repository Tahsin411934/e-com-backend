<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class ProductCategory extends Model
{
    use CustomSoftDeletes;

    protected $table = 'product_categories';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = ['product_id', 'category_id'];
}
