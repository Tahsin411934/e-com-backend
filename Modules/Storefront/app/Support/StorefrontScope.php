<?php

namespace Modules\Storefront\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\Store\Support\CurrentStore;

class StorefrontScope
{
    /**
     * "Visible on this storefront" filter: rows owned ONLY by the resolved
     * store. Global/platform rows (store_id IS NULL) are NOT included.
     */
    public static function apply(Builder $query, ?int $storeId = null): Builder
    {
        $storeId ??= CurrentStore::id();
        $column = $query->getModel()->qualifyColumn('store_id');

        return $query->where($column, $storeId);
    }
}
