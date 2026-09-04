<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;

trait BelongsToStore
{
    /**
     * Auto-assign store ownership on creation so a scoped owner's product
     * is never persisted without their store id.
     */
    public static function bootBelongsToStore(): void
    {
        static::creating(function ($model): void {
            if (empty($model->store_id)) {
                $model->store_id = CurrentStore::id();
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('store_id'), $storeId);
    }

    /**
     * Explicit scoping used by the admin/catalog layer only.
     * Public/frontend APIs never call this scope, so storefront
     * behaviour stays exactly as before.
     */
    public function scopeForCurrentStore(Builder $query): Builder
    {
        if (($storeId = CurrentStore::id()) !== null) {
            return $query->where($query->getModel()->qualifyColumn('store_id'), $storeId);
        }

        return $query;
    }
}
