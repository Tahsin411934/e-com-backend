<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class ProductRequest extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'product_requests';

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'product_name',
        'product_description',
        'product_image',
        'product_id',
        'quantity',
        'expected_price',
        'notes',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expected_price' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}