<?php

namespace Modules\Reviews\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Catalog\Models\Product;
use Modules\Reviews\Models\ProductReview;
use Modules\Reviews\Services\NotificationService;

class ProductReviewApiController extends Controller
{
    /**
     * Get approved reviews for a product.
     */
    public function index(Request $request, int $productId): JsonResponse
    {
        Product::findOrFail($productId);

        $reviews = ProductReview::where('product_id', $productId)
            ->where('status', 'approved')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        $formattedReviews = $reviews->map(function (ProductReview $review): array {
            return [
                'id' => $review->id,
                'user_name' => $review->user->name ?? 'Anonymous',
                'rating' => $review->rating,
                'title' => $review->title,
                'body' => $review->body,
                'is_verified_purchase' => $review->is_verified_purchase,
                'created_at' => $review->created_at->diffForHumans(),
            ];
        });

        return ApiResponse::fromResult([
            'success' => true,
            'data' => $formattedReviews,
            'average_rating' => round($reviews->avg('rating') ?? 0, 1),
            'total_reviews' => $reviews->count(),
        ]);
    }

    /**
     * Store a pending review for a product.
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        Product::findOrFail($productId);

        $validated = $request->validate([
            'product_id' => ['sometimes', 'integer', Rule::in([$productId])],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $existingReview = ProductReview::where('product_id', $productId)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            return ApiResponse::fromResult([
                'success' => false,
                'message' => 'You have already reviewed this product.',
            ], 200, 422);
        }

        $review = ProductReview::create([
            'product_id' => $productId,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'status' => 'pending',
            'is_verified_purchase' => false,
        ]);

        // In-app bell notification for every admin (user_id = null = broadcast).
        app(NotificationService::class)->notify(
            null,
            'review.created',
            'New product review received',
            sprintf(
                '%s left a %d★ review on %s: %s',
                $request->user()->name ?? 'A customer',
                $review->rating,
                Product::find($productId)?->name ?? ('Product #'.$productId),
                $review->title
            ),
            ['review_id' => $review->id, 'product_id' => $productId, 'url' => '/product-reviews'],
        );

        return ApiResponse::fromResult([
            'success' => true,
            'message' => 'Review submitted successfully. It will be published after approval.',
            'data' => [
                'id' => $review->id,
                'user_name' => $request->user()->name ?? 'Anonymous',
                'rating' => $review->rating,
                'title' => $review->title,
                'body' => $review->body,
                'is_verified_purchase' => $review->is_verified_purchase,
                'created_at' => $review->created_at->diffForHumans(),
            ],
        ], 201, 500);
    }
}
