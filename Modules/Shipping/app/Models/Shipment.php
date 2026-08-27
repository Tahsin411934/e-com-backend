<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Modules\Order\Models\Order;
use Modules\Store\Models\Address;
use Modules\Store\Models\Store;

class Shipment extends Model
{
    use HasFactory, CustomSoftDeletes;

    protected $table = 'shipments';

    protected $fillable = [
        'order_id',
        'store_id',
        'delivery_zone_id',
        'driver_id',
        'shipping_address_id',
        'tracking_number',
        'carrier_name',
        'service_level',
        'delivery_type',
        'status',
        'shipping_cost',
        'package_weight_kg',
        'package_count',
        'recipient_name',
        'recipient_phone',
        'delivery_instructions',
        'scheduled_delivery_date',
        'eta_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:4',
        'package_weight_kg' => 'decimal:2',
        'scheduled_delivery_date' => 'date',
        'eta_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function zone()
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function driver()
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }
}
