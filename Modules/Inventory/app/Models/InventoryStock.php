<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class InventoryStock extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

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
        return $this->belongsTo(\Modules\Catalog\Models\ProductVariant::class, 'variant_id');
    }

    public function variantOption()
    {
        return $this->belongsTo(\Modules\Catalog\Models\VariantOption::class, 'variant_option_id');
    }
}
