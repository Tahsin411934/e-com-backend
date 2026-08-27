<?php

namespace Modules\Reviews\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory, CustomSoftDeletes;

    protected $table = 'webhooks';

    protected $fillable = [
        'name', 'url', 'secret', 'events', 'status', 'retry_count', 'timeout_seconds', 'description',
    ];

    protected $casts = [
        'events' => 'json',
    ];

    public function deliveries() { return $this->hasMany(WebhookDelivery::class, 'webhook_id'); }
}