<?php

namespace Modules\Order\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Models\User;

class Delivery extends Model
{
    use CustomSoftDeletes, HasFactory;

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
        return $this->belongsTo(User::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }
}
