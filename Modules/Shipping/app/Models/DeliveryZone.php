<?php

namespace Modules\Shipping\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Store\Models\Country;
use Modules\Store\Models\Store;

class DeliveryZone extends Model
{
    use CustomSoftDeletes, HasFactory;

    protected $table = 'delivery_zones';

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'city',
        'state',
        'country_id',
        'postal_codes',
        'base_fee',
        'per_km_fee',
        'free_shipping_min',
        'max_delivery_distance_km',
        'estimated_min_days',
        'estimated_max_days',
        'status',
    ];

    protected $casts = [
        'postal_codes' => 'array',
        'base_fee' => 'decimal:4',
        'per_km_fee' => 'decimal:4',
        'free_shipping_min' => 'decimal:4',
        'max_delivery_distance_km' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function drivers()
    {
        return $this->hasMany(DeliveryDriver::class, 'delivery_zone_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'delivery_zone_id');
    }
}
