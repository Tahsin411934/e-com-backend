<?php

namespace Modules\Storefront\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Catalog\Models\Product;
use Modules\Frontend\Services\ProductSearchService as BaseProductSearchService;
use Modules\Store\Support\CurrentStore;

/**
 * Tenant-scoped storefront search.
 *
 * Reuses the Frontend module's fuzzy-search algorithm untouched — only the
 * candidate queries are post-filtered, so the search never leaves the
 * current tenant's catalog (plus global platform products, store_id NULL).
 */
class ProductSearchService extends BaseProductSearchService
{
    public function fulltextSearch(string $query, ?int $categoryId = null): Collection
    {
        return $this->filterToCurrentStore(parent::fulltextSearch($query, $categoryId));
    }

    public function fallbackLikeSearch(string $query, ?int $categoryId = null): Collection
    {
        return $this->filterToCurrentStore(parent::fallbackLikeSearch($query, $categoryId));
    }

    private function filterToCurrentStore(Collection $candidates): Collection
    {
        if ($candidates->isEmpty()) {
            return $candidates;
        }

        $storeId = CurrentStore::id();

        if ($storeId === null) {
            return $candidates;
        }

        $visibleIds = Product::query()
            ->whereIn('id', $candidates->pluck('id'))
            ->where(function ($query) use ($storeId) {
                $query->whereNull('store_id')->orWhere('store_id', $storeId);
            })
            ->pluck('id');

        return $candidates
            ->filter(fn ($candidate) => $visibleIds->contains($candidate->id))
            ->values();
    }
}
