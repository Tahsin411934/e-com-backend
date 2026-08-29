<?php

namespace Modules\Pos\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Catalog\Models\Product;

class PosSaleItem extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'pos_sale_items';

    protected $fillable = [
        'pos_sale_id',
        'product_id',
        'variant_id',
        'product_name',
        'sku',
        'unit_price',
        'quantity',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function posSale()
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
