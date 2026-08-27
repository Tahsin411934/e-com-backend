<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Wishlist extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'wishlists';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class);
    }

    public function product()
    {
        return $this->belongsTo(\Modules\Catalog\Models\Product::class);
    }
}