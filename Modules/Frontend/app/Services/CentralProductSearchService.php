<?php

namespace Modules\Frontend\Services;

use Illuminate\Database\Eloquent\Builder;

/** Product search for the central marketplace catalog only. */
class CentralProductSearchService extends ProductSearchService
{
    protected function productQuery(): Builder
    {
        return parent::productQuery()->whereNull('products.store_id');
    }
}
