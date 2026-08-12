<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountProductProfitSnapshot extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'account_product_profit_snapshots';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'pos_sale_id',
        'pos_sale_item_id',
        'product_id',
        'variant_id',
        'store_id',
        'sku',
        'product_name',
        'variant_name',
        'quantity',
        'unit_cost',
        'unit_sale_price',
        'gross_sales',
        'discount_total',
        'tax_total',
        'net_sales',
        'cost_total',
        'gross_profit',
        'profit_margin',
        'currency_code',
        'sold_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'unit_sale_price' => 'decimal:4',
        'gross_sales' => 'decimal:4',
        'discount_total' => 'decimal:4',
        'tax_total' => 'decimal:4',
        'net_sales' => 'decimal:4',
        'cost_total' => 'decimal:4',
        'gross_profit' => 'decimal:4',
        'profit_margin' => 'decimal:4',
        'sold_at' => 'datetime',
    ];
}
