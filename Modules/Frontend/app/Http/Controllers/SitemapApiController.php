<?php

namespace Modules\Frontend\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\SitemapCache;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Catalog\Models\Product;

/**
 * Next.js-এর চাঙ্কড সাইটম্যাপের ফিড।
 *
 * প্রতিটি চাঙ্ক পেজে শুধু { slug, updated_at } ফেরত দেওয়া হয় — তাই ২৫,০০০
 * রো-ও হালকা ও দ্রুত। SEO-ফিল্টার (active + public + published) এক জায়গায়
 * রাখা হয়েছে যাতে ফ্রন্টএন্ডে ডুপ্লিকেট লজিক না লাগে।
 *
 * ক্যাশ লেয়ার: Next.js ISR (১ ঘণ্টা) → HTTP s-maxage → Laravel App Cache।
 * ফলে গুগলবটের প্রতিটি হিট Laravel/MySQL-এ যাওয়ার দরকার হয় না।
 */
class SitemapApiController extends Controller
{
    /** এক রিকোয়েস্টে কখনো এর চেয়ে বড় চাঙ্ক দেব না (Google-এর 50k-লিমিটের অর্ধেক)। */
    protected const MAX_LIMIT = 25_000;

    /** Next.js-এর /sitemap.xml revalidate উইন্ডোর সাথে মিল (১ ঘণ্টা)। */
    protected const CACHE_TTL_SECONDS = 3_600;

    protected const CACHE_CONTROL =
        'public, max-age=300, s-maxage=3600, stale-while-revalidate=3600';

    /**
     * GET /v1/sitemap/products-count
     *
     * এনভেলপ: { status, message, data: { total, chunk_size, pages } }
     */
    public function productCount(Request $request): JsonResponse
    {
        $limit = $this->resolveLimit($request->query('limit', self::MAX_LIMIT));

        $total = Cache::remember(
            SitemapCache::key('products:count'),
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
     * GET /v1/sitemap/products?page=1&limit=25000
     *
     * এনভেলপ: { status, message, data: { items, page, limit, has_more } }
     */
    public function products(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = $this->resolveLimit($request->query('limit', 5_000));
        $offset = ($page - 1) * $limit;

        $payload = Cache::remember(
            SitemapCache::key("products:page:{$page}:{$limit}"),
            self::CACHE_TTL_SECONDS,
            fn () => $this->buildProductsPayload($page, $limit, $offset),
        );

        return response()->json($payload)->header('Cache-Control', self::CACHE_CONTROL);
    }

    /**
     * "কোন প্রোডাক্ট সাইটম্যাপে থাকবে" — একক SEO সংজ্ঞা।
     *
     *  - status      = active      → draft/আর্কাইভ বাদ
     *  - visibility  = public      → hidden/internal বাদ
     *  - published_at নট null      → এখনও publish হয়নি এমন বাদ
     *  - deleted_at  → SoftDeletes ট্রেইট (CustomSoftDeletes) অটো বাদ দেয়
     */
    private function indexableProducts(): Builder
    {
        return Product::query()
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->whereNotNull('published_at');
    }

    private function resolveLimit(mixed $value): int
    {
        return min(max((int) $value, 1), self::MAX_LIMIT);
    }

    /**
     * PRIMARY KEY (id) দিয়ে ORDER → ইনডেক্সড + স্থিতিশীল offset-pagination।
     * মাঝে নতুন প্রোডাক্ট ঢুকলেও পেজ ড্রিফট হয় না (updated_at-ORDER-এর বিপরীতে)।
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
