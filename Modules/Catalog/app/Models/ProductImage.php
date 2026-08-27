<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class ProductImage extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'product_images';

    protected $fillable = ['product_id', 'variant_id', 'image_url', 'alt_text', 'sort_order', 'is_main'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getImageUrlAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return asset($value);
        }

        return asset('storage/' . ltrim($value, '/'));
    }
}
