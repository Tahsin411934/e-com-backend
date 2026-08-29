<?php

namespace Modules\Inventory\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;

class InventoryStock extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'inventory_stock';

    protected $fillable = [
        'location_id',
        'variant_id',
        'variant_option_id',
        'quantity_on_hand',
        'quantity_reserved',
        'reorder_point',
    ];

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function variantOption()
    {
        return $this->belongsTo(VariantOption::class, 'variant_option_id');
    }
}
