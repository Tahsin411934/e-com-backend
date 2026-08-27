<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Modules\Identity\Models\User;

class ShipmentEvent extends Model
{
    use HasFactory, CustomSoftDeletes;

    protected $table = 'shipment_events';

    protected $fillable = [
        'shipment_id',
        'driver_id',
        'created_by',
        'event_type',
        'status',
        'title',
        'description',
        'latitude',
        'longitude',
        'occurred_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'occurred_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function driver()
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
