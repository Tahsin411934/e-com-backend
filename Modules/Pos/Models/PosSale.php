<?php

namespace Modules\Pos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Identity\Models\User;
use Modules\Order\Models\Order;

class PosSale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pos_sales';

    protected $fillable = [
        'register_id',
        'shift_id',
        'order_id',
        'user_id',
        'receipt_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'cash_amount',
        'card_amount',
        'other_amount',
        'change_amount',
        'payment_status',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'card_amount' => 'decimal:2',
        'other_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function register()
    {
        return $this->belongsTo(PosRegister::class, 'register_id');
    }

    public function shift()
    {
        return $this->belongsTo(PosShift::class, 'shift_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(PosSaleItem::class, 'pos_sale_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
