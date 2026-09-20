<?php

namespace Modules\Storefront\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Store\Support\CurrentStore;

class StorefrontScope
{
    /**
     * "Visible on this storefront" filter: only rows owned by the resolved
     * store are visible. Global/platform rows never leak into a tenant.
     */
    public static function apply(Builder|Relation $query, ?int $storeId = null): Builder|Relation
    {
        $storeId ??= CurrentStore::id();
        $model = $query instanceof Builder ? $query->getModel() : $query->getRelated();
        $column = $model->qualifyColumn('store_id');

        return $query->where($column, $storeId);
    }
}
