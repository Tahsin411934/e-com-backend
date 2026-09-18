<?php

namespace Modules\Storefront\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\Store\Support\CurrentStore;

class StorefrontScope
{
    /**
     * "Visible on this storefront" filter: rows owned by the resolved
     * store plus global platform rows (store_id IS NULL).
     *
     * Convention from the store_id migrations: NULL store_id = global /
     * platform content, so a tenant storefront shows the platform seed
     * content until the owner creates their own.
     */
    public static function apply(Builder $query, ?int $storeId = null): Builder
    {
        $storeId ??= CurrentStore::id();
        $column = $query->getModel()->qualifyColumn('store_id');

        return $query->where(function (Builder $q) use ($column, $storeId) {
            $q->whereNull($column);

            if ($storeId !== null) {
                $q->orWhere($column, $storeId);
            }
        });
    }
}
