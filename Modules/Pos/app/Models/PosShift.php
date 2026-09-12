<?php

namespace Modules\Pos\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Models\User;

class PosShift extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'pos_shifts';

    protected $fillable = [
        'register_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'cash_sales',
        'card_sales',
        'other_sales',
        'total_sales',
        'declared_cash',
        'discrepancy',
        'notes',
        'status',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'other_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'declared_cash' => 'decimal:2',
        'discrepancy' => 'decimal:2',
    ];

    public function register()
    {
        return $this->belongsTo(PosRegister::class, 'register_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(PosSale::class, 'shift_id');
    }
}
