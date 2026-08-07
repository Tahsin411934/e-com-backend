<?php

namespace Modules\Cart\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Models\Wishlist;
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
                    'id' => $wishlist->id,
                    'edit' => 'wishlistEdit',
                    'delete' => 'wishlistDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveWishlist(array $data): array
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

                return [
                    'status' => 'success',
                    'message' => $message,
                    'wishlist' => $wishlist->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving wishlist: ' . $e->getMessage(),
            ];
        }
    }

    public function getWishlistById(int $id): array
    {
        try {
            $wishlist = Wishlist::with('product')->findOrFail($id);
            return [
                'status' => 'success',
                'wishlist' => $wishlist,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Wishlist not found.',
            ];
        }
    }

    public function deleteWishlist(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $wishlist = Wishlist::findOrFail($id);
                $wishlist->delete();

                return [
                    'status' => 'success',
                    'message' => 'Wishlist deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting wishlist: ' . $e->getMessage(),
            ];
        }
    }

    public function toggleWishlist(int $productId): array
    {
        try {
            return DB::transaction(function () use ($productId) {
                $userId = auth()->id();

                if (!$userId) {
                    return [
                        'status' => 'error',
                        'message' => 'Please login to add to wishlist.',
                    ];
                }

                $existing = Wishlist::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->first();

                if ($existing) {
                    $existing->delete();
                    return [
                        'status' => 'success',
                        'message' => 'Removed from wishlist.',
                        'action' => 'removed',
                    ];
                }

                Wishlist::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                ]);

                return [
                    'status' => 'success',
                    'message' => 'Added to wishlist.',
                    'action' => 'added',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error toggling wishlist: ' . $e->getMessage(),
            ];
        }
    }

    public function getCurrentUserWishlist(): array
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return ['status' => 'error', 'message' => 'Unauthenticated'];
            }

            $items = Wishlist::with('product')
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->get();

            return [
                'status' => 'success',
                'wishlist' => $items,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error loading wishlist: ' . $e->getMessage(),
            ];
        }
    }

    public function removeWishlistItem(int $userId, int $productId): array
    {
        try {
            return DB::transaction(function () use ($userId, $productId) {
                $item = Wishlist::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->first();

                if (!$item) {
                    return [
                        'status' => 'error',
                        'message' => 'Wishlist item not found.',
                    ];
                }

                $item->delete();

                return [
                    'status' => 'success',
                    'message' => 'Wishlist item removed successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error removing wishlist item: ' . $e->getMessage(),
            ];
        }
    }
}