<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSubscription extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'plan_id', 'status', 'starts_at', 'ends_at', 'provider', 'provider_subscription_id', 'metadata'];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'metadata' => 'array'];

    public function store() { return $this->belongsTo(Store::class); }
    public function plan() { return $this->belongsTo(Plan::class); }

    public function isUsable(): bool
    {
        return in_array($this->status, ['trialing', 'active'], true)
            && (! $this->ends_at || $this->ends_at->isFuture());
    }
}
