<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Category;
use Modules\Frontend\Http\Resources\HomeProductResource;
use Modules\Frontend\Models\HomepageCta;
use Modules\Storefront\Support\StorefrontScope;

/**
 * Storefront home page API.
 *
 * Mirrors the legacy /v1/home/products-by-category payload (category sections
 * interleaved with CTA sections), but only for the resolved tenant: this
 * store's categories/CTAs first, falling back to global platform content.
 */
class HomeController extends Controller
{
    /**
     * @queryParam limit_categories int Max categories to show. Default: 10
     * @queryParam limit_products int Max products per category. Default: 8
     */
    public function productsByCategory(Request $request): JsonResponse
    {
        $limitCategories = min((int) $request->query('limit_categories', 10), 50);
        $limitProducts = min((int) $request->query('limit_products', 8), 20);

        $data = $this->buildHomePageData($limitCategories, $limitProducts);

        return ApiResponse::success($data, 'Home page data retrieved successfully.');
    }

    /**
     * Build the interleaved home page sections for the current storefront.
     */
    private function buildHomePageData(int $limitCategories, int $limitProducts): array
    {
        // 1. Active categories that have visible products on this storefront.
        $categoryQuery = Category::where('status', 'active')
            ->withCount(['products' => fn (Builder $q) => StorefrontScope::apply($q)])
            ->having('products_count', '>', 0)
            ->orderBy('sort_order')
            ->orderBy('name');

        StorefrontScope::apply($categoryQuery);

        $categories = $categoryQuery->take($limitCategories)->get();

        // 2. Active CTAs of this storefront.
        $ctaQuery = HomepageCta::where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id');

        StorefrontScope::apply($ctaQuery);

        $ctas = $ctaQuery->get();

        // 3. Products per category (each section shows only its own products).
        $productsByCategory = [];

        foreach ($categories as $category) {
            $query = $category->products()
                ->where('products.status', 'active')
                ->where('products.visibility', 'public')
                ->with([
                    'images',
                    'variants' => function ($q) {
                        $q->where('status', 'active');
                    },
                ]);

            StorefrontScope::apply($query);

            $hasHomepageProducts = (clone $query)
                ->where('products.is_homepage', true)
                ->exists();

            if ($hasHomepageProducts) {
                $query->where('products.is_homepage', true);
            }

            $productsByCategory[$category->id] = $query
                ->orderBy('products.order_column')
                ->orderBy('products.published_at', 'desc')
                ->limit($limitProducts)
                ->get();
        }

        return $this->buildSections($categories, $productsByCategory, $ctas, $limitProducts);
    }

    /**
     * Interleave category sections with CTA sections (a CTA after every 2
     * category sections), exactly like the legacy home API.
     */
    private function buildSections($categories, array $productsByCategory, $ctas, int $limitProducts): array
    {
        $sections = [];
        $ctaIndex = 0;

        foreach ($categories as $i => $category) {
            $categoryProducts = isset($productsByCategory[$category->id])
                ? $productsByCategory[$category->id]->take($limitProducts)
                : collect();

            $categoryImage = null;
            if ($category->image) {
                $categoryImage = asset('storage/'.$category->image);
            } elseif ($category->image_url) {
                $categoryImage = $category->image_url;
            }

            $sections[] = [
                'type' => 'category_section',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $categoryImage,
                ],
                'products' => HomeProductResource::collection($categoryProducts),
            ];

            // Insert a CTA section after every 2 category sections (if available)
            if (($i + 1) % 2 === 0 && isset($ctas[$ctaIndex])) {
                $sections[] = $this->ctaSection($ctas[$ctaIndex]);
                $ctaIndex++;
            }
        }

        return $sections;
    }

    /**
     * Single CTA section payload — identical field set to the legacy API.
     */
    private function ctaSection(HomepageCta $cta): array
    {
        return [
            'type' => 'cta_section',
            'id' => $cta->id,
            'cta_style' => $cta->cta_style ?? 'style1',
            'title' => $cta->title,
            'subtitle' => $cta->subtitle,
            'description' => $cta->description,
            'image' => $cta->image ? asset('storage/'.$cta->image) : null,
            'banner_image' => $cta->banner_image ? asset('storage/'.$cta->banner_image) : null,
            'button_text' => $cta->button_text,
            'button_link' => $cta->button_link,
            'background_color' => $cta->background_color,
            'text_color' => $cta->text_color,
            'button_color' => $cta->button_color,
            'button_text_color' => $cta->button_text_color,
            'overlay_color' => $cta->overlay_color,
            'badge_text' => $cta->badge_text,
            'badge_color' => $cta->badge_color,
            'secondary_button_text' => $cta->secondary_button_text,
            'secondary_button_link' => $cta->secondary_button_link,
            'secondary_button_color' => $cta->secondary_button_color,
            'secondary_button_text_color' => $cta->secondary_button_text_color,
            'feature_icon_1' => $cta->feature_icon_1,
            'feature_text_1' => $cta->feature_text_1,
            'feature_icon_2' => $cta->feature_icon_2,
            'feature_text_2' => $cta->feature_text_2,
            'feature_icon_3' => $cta->feature_icon_3,
            'feature_text_3' => $cta->feature_text_3,
            // Dynamic positioning & margin
            'button_position' => $cta->button_position,
            'button_margin_top' => $cta->button_margin_top,
            'button_margin_bottom' => $cta->button_margin_bottom,
            'button_margin_left' => $cta->button_margin_left,
            'button_margin_right' => $cta->button_margin_right,
            'secondary_button_position' => $cta->secondary_button_position,
            'secondary_button_margin_top' => $cta->secondary_button_margin_top,
            'secondary_button_margin_bottom' => $cta->secondary_button_margin_bottom,
            'secondary_button_margin_left' => $cta->secondary_button_margin_left,
            'secondary_button_margin_right' => $cta->secondary_button_margin_right,
            'content_alignment' => $cta->content_alignment,
            'content_margin_top' => $cta->content_margin_top,
            'content_margin_bottom' => $cta->content_margin_bottom,
            'content_margin_left' => $cta->content_margin_left,
            'content_margin_right' => $cta->content_margin_right,
        ];
    }
}