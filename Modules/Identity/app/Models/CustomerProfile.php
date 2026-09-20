<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'avatar',
        'date_of_birth',
        'gender',
        'marketing_consent',
        'last_order_at',
        'total_orders',
        'total_spent',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'marketing_consent' => 'boolean',
        'last_order_at' => 'datetime',
        'total_orders' => 'integer',
        'total_spent' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
