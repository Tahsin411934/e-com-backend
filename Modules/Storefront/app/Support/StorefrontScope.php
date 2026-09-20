<?php

namespace Modules\Storefront\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Store\Support\CurrentStore;

class StorefrontScope
{
    /**
     * "Visible on this storefront" filter: store-owned rows take part in the
     * storefront, together with global/platform rows used as fallback content.
     */
    public static function apply(Builder|Relation $query, ?int $storeId = null): Builder|Relation
    {
        $storeId ??= CurrentStore::id();
        $model = $query instanceof Builder ? $query->getModel() : $query->getRelated();
        $column = $model->qualifyColumn('store_id');

        return $query->where(function ($query) use ($column, $storeId) {
            $query->where($column, $storeId)->orWhereNull($column);
        });
    }
}
