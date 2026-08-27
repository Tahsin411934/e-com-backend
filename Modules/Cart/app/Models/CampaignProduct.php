<?php

namespace Modules\Cart\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CampaignProduct extends Model
{
    use CustomSoftDeletes;

    protected $fillable = ['campaign_id', 'product_id', 'variant_id', 'discount_type', 'discount_value', 'sort_order'];
    protected $casts = ['discount_value' => 'decimal:4', 'sort_order' => 'integer'];
    public function campaign() { return $this->belongsTo(Campaign::class); }
    public function product() { return $this->belongsTo(\Modules\Catalog\Models\Product::class); }
    public function variant() { return $this->belongsTo(\Modules\Catalog\Models\ProductVariant::class); }
}
