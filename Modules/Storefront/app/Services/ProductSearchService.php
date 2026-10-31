<?php

namespace Modules\Storefront\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Services\ProductSearchService as BaseProductSearchService;
use Modules\Storefront\Support\StorefrontScope;

/** Apply tenant filtering before candidate limits, ranking, and final hydration. */
class ProductSearchService extends BaseProductSearchService
{
    protected function productQuery(): Builder
    {
        return StorefrontScope::apply(parent::productQuery());
    }

    public function fulltextSearch(string $query, ?int $categoryId = null): Collection
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $this->fallbackLikeSearch($query, $categoryId);
        }

        return parent::fulltextSearch($query, $categoryId);
    }

    public function fallbackLikeSearch(string $query, ?int $categoryId = null): Collection
    {
        return parent::fallbackLikeSearch($query, $categoryId);
    }
}
