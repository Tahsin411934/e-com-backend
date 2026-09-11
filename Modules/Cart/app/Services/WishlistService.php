<?php

namespace Modules\Cart\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Models\Wishlist;
use Modules\Frontend\Services\ProductPricingService;
use Yajra\DataTables\DataTables;

class WishlistService
{
    public function getWishlistDataTable(Request $request)
    {
        $query = Wishlist::with('product')
            ->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('user_email', function (Wishlist $wishlist) {
                return $wishlist->user?->email ?? '-';
            })
            ->addColumn('product_name', function (Wishlist $wishlist) {
                return $wishlist->product?->name ?? '-';
            })
            ->editColumn('created_at', function (Wishlist $wishlist) {
                return $wishlist->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Wishlist $wishlist) {
                return view('components.action-buttons', [
                'permission' => 'wishlists',
                'entityLabel' => 'Wishlist',
                    'id' => $wishlist->id,
                    'edit' => 'wishlistEdit',
                    'delete' => 'wishlistDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveWishlist(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $wishlistId = $data['wishlist_id'] ?? null;
                unset($data['wishlist_id']);

                if ($wishlistId) {
                    $wishlist = Wishlist::findOrFail($wishlistId);
                    $wishlist->update($data);
                    $message = 'Wishlist updated successfully.';
                } else {
                    $wishlist = Wishlist::create($data);
                    $message = 'Wishlist created successfully.';
                }

                return ApiResponse::success($wishlist->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving wishlist: '.$e->getMessage(), 500);
        }
    }

    public function getWishlistById(int $id): JsonResponse
    {
        try {
            $wishlist = Wishlist::with('product')->findOrFail($id);

            return ApiResponse::success($wishlist);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Wishlist not found.');
        }
    }

    public function deleteWishlist(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $wishlist = Wishlist::findOrFail($id);
                $wishlist->delete();

                return ApiResponse::success(null, 'Wishlist deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting wishlist: '.$e->getMessage(), 500);
        }
    }

    public function toggleWishlist(int $productId): JsonResponse
    {
        try {
            return DB::transaction(function () use ($productId) {
                $userId = auth()->id();

                if (! $userId) {
                    return ApiResponse::error('Please login to add to wishlist.', 500);
                }

                $existing = Wishlist::withTrashed()
                    ->where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->first();

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();

                        return ApiResponse::success('added', 'Added to wishlist.');
                    }

                    $existing->delete();

                    return ApiResponse::success('removed', 'Removed from wishlist.');
                }

                Wishlist::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                ]);

                return ApiResponse::success('added', 'Added to wishlist.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error toggling wishlist: '.$e->getMessage(), 500);
        }
    }

    public function getCurrentUserWishlist(): JsonResponse
    {
        try {
            $userId = auth()->id();
            if (! $userId) {
                return ApiResponse::error('Unauthenticated', 500);
            }

            $items = Wishlist::with(['product.images', 'product.variants' => fn ($q) => $q->where('status', 'active')])
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->get();

            $pricing = app(ProductPricingService::class);

            $wishlist = $items->map(function (Wishlist $item) use ($pricing) {
                $product = $item->product;
                $priceInfo = $product ? $pricing->priceInfo($product) : null;
                $mainImage = $product?->images->firstWhere('is_main', true) ?? $product?->images->first();

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'created_at' => $item->created_at,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'main_image' => $mainImage?->image_url ? asset('storage/'.ltrim($mainImage->image_url, '/')) : null,
                        'price' => $priceInfo['price'],
                        'regular_price' => $priceInfo['regular_price'],
                        'discount_percent' => $priceInfo['discount_percent'],
                        'discount_amount' => $priceInfo['discount_amount'],
                        'has_discount' => $priceInfo['has_discount'],
                    ],
                ];
            });

            return [
                'status' => 'success',
                'wishlist' => $wishlist->values()->all(),
            ];
        } catch (\Exception $e) {
            return ApiResponse::error('Error loading wishlist: '.$e->getMessage(), 500);
        }
    }

    public function removeWishlistItem(int $userId, int $productId): JsonResponse
    {
        try {
            return DB::transaction(function () use ($userId, $productId) {
                $item = Wishlist::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->first();

                if (! $item) {
                    return ApiResponse::notFound('Wishlist item not found.');
                }

                $item->delete();

                return ApiResponse::success(null, 'Wishlist item removed successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error removing wishlist item: '.$e->getMessage(), 500);
        }
    }
}
