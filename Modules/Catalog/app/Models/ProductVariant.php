<?php

namespace Modules\Catalog\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\Models\InventoryStock;

class ProductVariant extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'product_variants';

    protected $fillable = ['product_id', 'sku', 'barcode', 'name', 'attributes', 'cost_price', 'sale_price', 'discount_percent', 'compare_at_price', 'weight_grams', 'length_mm', 'width_mm', 'height_mm', 'track_inventory', 'allow_backorder', 'status'];

    protected $casts = [
        'attributes' => 'array',
        'discount_percent' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class, 'variant_id');
    }

    public function options()
    {
        return $this->hasMany(VariantOption::class, 'product_variant_id');
    }

    public function getStockAttribute(): int
    {
        return $this->inventoryStocks
            ->sum(fn ($stock) => max(0, $stock->quantity_on_hand - $stock->quantity_reserved));
    }
}
