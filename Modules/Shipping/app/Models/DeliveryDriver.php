<?php

namespace Modules\Shipping\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;

class DeliveryDriver extends Model
{
    use CustomSoftDeletes, HasFactory;

    protected $table = 'delivery_drivers';

    protected $fillable = [
        'user_id',
        'store_id',
        'delivery_zone_id',
        'employee_code',
        'name',
        'phone',
        'email',
        'license_number',
        'vehicle_type',
        'vehicle_plate',
        'capacity_kg',
        'status',
        'current_latitude',
        'current_longitude',
        'last_seen_at',
    ];

    protected $casts = [
        'capacity_kg' => 'decimal:2',
        'current_latitude' => 'decimal:7',
        'current_longitude' => 'decimal:7',
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function zone()
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'driver_id');
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class, 'driver_id');
    }
}
