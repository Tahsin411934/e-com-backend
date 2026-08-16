<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'banner_image', 'button_text', 'priority', 'is_featured', 'is_active', 'status', 'starts_at', 'ends_at'];
    protected $casts = ['is_featured' => 'boolean', 'is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function products() { return $this->hasMany(CampaignProduct::class); }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'active')->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
