<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\SitemapCache;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Catalog\Models\Product;
use Modules\Store\Support\CurrentStore;
use Modules\Storefront\Support\StorefrontScope;

/**
 * Per-storefront sitemap feed (Next.js chunked sitemap).
 *
 * Same contract as the legacy /v1/sitemap/* endpoints, with two tenant
 * guarantees:
 *
 *  1. Only products visible on this storefront are listed (store + global rows).
 *  2. Cache keys are namespaced per store, so one tenant's chunk can never be
 *     served to another tenant.
 */
class SitemapController extends Controller
{
    /** Hard cap per request (half of Google's 50k limit). */
    protected const MAX_LIMIT = 25_000;

    /** Matches Next.js's 1-hour revalidate window. */
    protected const CACHE_TTL_SECONDS = 3_600;

    protected const CACHE_CONTROL =
        'public, max-age=300, s-maxage=3600, stale-while-revalidate=3600';

    /**
     * GET /v1/storefront/sitemap/products-count
     */
    public function productCount(Request $request): JsonResponse
    {
        $limit = $this->resolveLimit($request->query('limit', self::MAX_LIMIT));

        $total = Cache::remember(
            $this->cacheKey('products:count'),
            self::CACHE_TTL_SECONDS,
            fn () => $this->indexableProducts()->count(),
        );

        return ApiResponse::success(
            [
                'total' => $total,
                'chunk_size' => $limit,
                'pages' => (int) ceil($total / max(1, $limit)),
            ],
            'Sitemap product count retrieved successfully.',
        )->header('Cache-Control', self::CACHE_CONTROL);
    }

    /**
     * GET /v1/storefront/sitemap/products?page=1&limit=5000
     */
    public function products(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = $this->resolveLimit($request->query('limit', 5_000));
        $offset = ($page - 1) * $limit;

        $payload = Cache::remember(
            $this->cacheKey("products:page:{$page}:{$limit}"),
            self::CACHE_TTL_SECONDS,
            fn () => $this->buildProductsPayload($page, $limit, $offset),
        );

        return response()->json($payload)->header('Cache-Control', self::CACHE_CONTROL);
    }

    /**
     * Indexable = active + public + published, for this storefront only.
     */
    private function indexableProducts(): Builder
    {
        $query = Product::query()
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->whereNotNull('published_at');

        return StorefrontScope::apply($query);
    }

    private function cacheKey(string $key): string
    {
        return SitemapCache::key('store:'.(CurrentStore::id() ?? 'global').':'.$key);
    }

    private function resolveLimit(mixed $value): int
    {
        return min(max((int) $value, 1), self::MAX_LIMIT);
    }

    /**
     * Primary-key ordering → indexed + stable offset pagination (new products
     * cannot shift the pages of an already-crawled sitemap).
     */
    private function buildProductsPayload(int $page, int $limit, int $offset): array
    {
        $rows = $this->indexableProducts()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return [
            'status' => 'success',
            'message' => 'Sitemap products retrieved successfully.',
            'data' => [
                'items' => $rows
                    ->filter(fn ($product) => filled($product->slug))
                    ->map(function ($product) {
                        $updatedAt = $product->updated_at;

                        return [
                            'slug' => $product->slug,
                            'updated_at' => $updatedAt ? $updatedAt->format('Y-m-d\TH:i:sP') : null,
                        ];
                    })
                    ->values(),
                'page' => $page,
                'limit' => $limit,
                'has_more' => $rows->count() === $limit,
            ],
        ];
    }
}