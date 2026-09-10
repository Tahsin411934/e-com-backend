<?php

namespace Modules\Catalog\Models;

use App\Traits\BelongsToStore;
use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToStore;
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['store_id', 'parent_id', 'name', 'slug', 'image', 'description', 'image_url', 'sort_order', 'status'];

    public function store()
    {
        return $this->belongsTo(\Modules\Store\Models\Store::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }
}
