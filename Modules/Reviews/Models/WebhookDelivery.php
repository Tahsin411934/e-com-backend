<?php

namespace Modules\Reviews\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use CustomSoftDeletes, HasFactory;

    protected $table = 'webhook_deliveries';

    protected $fillable = [
        'webhook_id', 'event', 'payload', 'response', 'response_status', 'attempt', 'success', 'delivered_at',
    ];

    protected $casts = [
        'payload' => 'json',
        'response' => 'json',
        'success' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }
}
