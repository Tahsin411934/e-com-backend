<?php

namespace Modules\Reviews\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Catalog\Models\Product;
use Modules\Identity\Models\User;

class ProductReview extends Model
{
    use HasFactory, CustomSoftDeletes;

    protected $table = 'product_reviews';

    protected $fillable = [
        'product_id', 'user_id', 'order_id', 'rating', 'title', 'body', 'status', 'is_verified_purchase',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
}