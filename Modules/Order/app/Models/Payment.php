<?php

namespace Modules\Order\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use CustomSoftDeletes, HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'order_id', 'provider', 'provider_payment_id', 'method',
        'status', 'amount', 'currency_code', 'paid_at', 'raw_response',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:4',
        'raw_response' => 'json',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
