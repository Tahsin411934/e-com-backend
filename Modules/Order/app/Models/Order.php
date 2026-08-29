<?php

namespace Modules\Order\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Cart\Models\Coupon;
use Modules\Identity\Models\User;
use Modules\Shipping\Models\Shipment;
use Modules\Store\Models\Address;
use Modules\Store\Models\Store;

class Order extends Model
{
    use CustomSoftDeletes, HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_number', 'user_id', 'store_id', 'source', 'status',
        'payment_status', 'fulfillment_status', 'currency_code',
        'subtotal', 'discount_total', 'tax_total', 'shipping_total', 'grand_total',
        'coupon_id', 'billing_address_id', 'shipping_address_id',
        'customer_note', 'placed_at', 'cancelled_at',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:4',
        'discount_total' => 'decimal:4',
        'tax_total' => 'decimal:4',
        'shipping_total' => 'decimal:4',
        'grand_total' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }
}
