<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Refund extends Model
{
    use HasFactory, CustomSoftDeletes;

    protected $table = 'refunds';

    protected $fillable = [
        'payment_id', 'order_id', 'amount', 'reason', 'status', 'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'decimal:4',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}