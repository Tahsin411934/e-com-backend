<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Delivery extends Model
{
    use HasFactory, CustomSoftDeletes;

    protected $table = 'deliveries';

    protected $fillable = [
        'order_id',
        'user_id',
        'delivery_boy_id',
        'status',
        'delivery_address',
        'delivery_city',
        'delivery_phone',
        'delivery_notes',
        'assigned_at',
        'picked_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'picked_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'delivery_boy_id');
    }
}