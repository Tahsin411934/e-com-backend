<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariantOption extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'variant_options';

    protected $fillable = [
        'product_variant_id',
        'color_name',
        'color_code',
        'sku',
        'barcode',
        'image_url',
        'price_adjustment',
        'cost_price',
        'sale_price',
        'compare_at_price',
        'discount_percent',
        'stock',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:4',
        'cost_price' => 'decimal:4',
        'sale_price' => 'decimal:4',
        'compare_at_price' => 'decimal:4',
        'discount_percent' => 'decimal:2',
        'stock' => 'integer',
        'sort_order' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function inventoryStocks()
    {
        return $this->hasMany(\Modules\Inventory\Models\InventoryStock::class, 'variant_option_id');
    }

    public function getStockAttribute(): int
    {
        return $this->inventoryStocks
            ->sum(fn ($stock) => max(0, $stock->quantity_on_hand - $stock->quantity_reserved));
    }
}