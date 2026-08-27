<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Coupon extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'coupons';

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'minimum_order_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'discount_value' => 'decimal:4',
        'minimum_order_amount' => 'decimal:4',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active'
            && (!$this->starts_at || $this->starts_at->isPast())
            && (!$this->ends_at || $this->ends_at->isFuture());
    }
}