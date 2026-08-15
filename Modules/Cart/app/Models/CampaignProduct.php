<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignProduct extends Model
{
    protected $fillable = ['campaign_id', 'product_id', 'variant_id', 'discount_type', 'discount_value'];
    protected $casts = ['discount_value' => 'decimal:4'];
    public function campaign() { return $this->belongsTo(Campaign::class); }
    public function product() { return $this->belongsTo(\Modules\Catalog\Models\Product::class); }
    public function variant() { return $this->belongsTo(\Modules\Catalog\Models\ProductVariant::class); }
}
