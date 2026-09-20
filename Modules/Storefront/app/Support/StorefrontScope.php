<?php

namespace Modules\Storefront\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\Store\Support\CurrentStore;

class StorefrontScope
{
    /**
     * "Visible on this storefront" filter: store-owned rows take part in the
     * storefront, together with global/platform rows used as fallback content.
     */
    public static function apply(Builder $query, ?int $storeId = null): Builder
    {
        $storeId ??= CurrentStore::id();
        $column = $query->getModel()->qualifyColumn('store_id');

        return $query->where(function (Builder $query) use ($column, $storeId) {
            $query->where($column, $storeId)->orWhereNull($column);
        });
    }
}
