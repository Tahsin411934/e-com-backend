<?php

namespace Modules\Cart\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;

class CartItem extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'variant_id',
        'variant_option_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:4',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function variantOption()
    {
        return $this->belongsTo(VariantOption::class, 'variant_option_id');
    }

    public function getLineTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }
}
