<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'price', 'currency', 'duration_days', 'product_limit', 'is_free', 'is_public', 'is_active', 'features'];

    protected $casts = ['price' => 'decimal:2', 'is_free' => 'boolean', 'is_public' => 'boolean', 'is_active' => 'boolean', 'features' => 'array'];

    public function subscriptions()
    {
        return $this->hasMany(StoreSubscription::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot(['enabled', 'limit_value', 'configuration'])->withTimestamps();
    }

    public function hasFeature(string $slug): bool
    {
        return $this->features()->where('features.slug', $slug)->wherePivot('enabled', true)->exists();
    }
}
