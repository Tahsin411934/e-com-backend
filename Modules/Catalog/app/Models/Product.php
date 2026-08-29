<?php

namespace Modules\Catalog\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\SubnavbarItem;
use Modules\Reviews\Models\ProductReview;

class Product extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'products';

    protected $fillable = ['brand_id', 'category_id', 'unit_id', 'size_id', 'tax_rate_id', 'navbar_item_id', 'subnavbar_item_id', 'name', 'slug', 'short_description', 'description', 'product_type', 'status', 'visibility', 'seo_title', 'seo_description', 'published_at', 'is_homepage', 'order_column'];

    protected $casts = [
        'published_at' => 'datetime',
        'is_homepage' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function navbarItem()
    {
        return $this->belongsTo(NavbarItem::class, 'navbar_item_id');
    }

    public function subnavbarItem()
    {
        return $this->belongsTo(SubnavbarItem::class, 'subnavbar_item_id');
    }

    /**
     * Scope to retrieve products ordered by order_column.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_column');
    }
}
