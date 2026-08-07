<?php

namespace Modules\Cart\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Models\Coupon;
use Yajra\DataTables\DataTables;

class CartService
{
    public function getCartDataTable(Request $request)
    {
        $query = Cart::query()
            ->with(['user', 'store', 'items'])
            ->withCount('items as items_count')
            ->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('user_email', function (Cart $cart) {
                return $cart->user?->email ?? '-';
            })
            ->addColumn('store_name', function (Cart $cart) {
                return $cart->store?->name ?? '-';
            })
            ->editColumn('total', function (Cart $cart) {
                return number_format($cart->total, 2);
            })
            ->editColumn('status', function (Cart $cart) {
                return ucfirst($cart->status);
            })
            ->editColumn('created_at', function (Cart $cart) {
                return $cart->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Cart $cart) {
                return view('components.action-buttons', [
                    'id' => $cart->id,
                    'edit' => 'cartEdit',
                    'delete' => 'cartDelete',
                ])->render();
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function saveCart(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $cartId = $data['cart_id'] ?? null;
                unset($data['cart_id']);

                if ($cartId) {
                    $cart = Cart::findOrFail($cartId);
                    $cart->update($data);
                    $message = 'Cart updated successfully.';
                } else {
                    // Fix the import - ProductVariant was removed from imports
                    $data['user_id'] = $data['user_id'] ?? auth()->id();
                    $data['expires_at'] = now()->addDays(7);
                    $cart = Cart::create($data);
                    $message = 'Cart created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'cart' => $cart->fresh()->load('items'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving cart: ' . $e->getMessage(),
            ];
        }
    }

    public function getCartById(int $id): array
    {
        try {
            $cart = Cart::with(['user', 'store', 'items'])->findOrFail($id);
            return [
                'status' => 'success',
                'cart' => $cart,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cart not found.',
            ];
        }
    }

    public function deleteCart(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $cart = Cart::findOrFail($id);
                $cart->delete();

                return [
                    'status' => 'success',
                    'message' => 'Cart deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting cart: ' . $e->getMessage(),
            ];
        }
    }

    public function getOrCreateCart(int $userId, ?int $storeId = null): Cart
    {
        $cart = Cart::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'store_id' => $storeId,
                'status' => 'active',
                'expires_at' => now()->addDays(7),
            ]);
        }

        return $cart;
    }

    public function addToCart(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $cart = $this->getOrCreateCart($data['user_id'], $data['store_id'] ?? null);

                // Find the variant - using direct class reference since import removed
                $variant = \Modules\Catalog\Models\ProductVariant::findOrFail($data['variant_id']);

                // Calculate price with variant option adjustment if provided
                $unitPrice = $variant->sale_price;
                $variantOptionId = $data['variant_option_id'] ?? null;
                
                if ($variantOptionId) {
                    $variantOption = \Modules\Catalog\Models\VariantOption::find($variantOptionId);
                    if ($variantOption && $variantOption->product_variant_id === $variant->id) {
                        $unitPrice += $variantOption->price_adjustment;
                    }
                }

                // Check for existing item with same variant AND variant_option
                $existingItem = CartItem::where('cart_id', $cart->id)
                    ->where('variant_id', $data['variant_id'])
                    ->where('variant_option_id', $variantOptionId)
                    ->first();

                if ($existingItem) {
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + ($data['quantity'] ?? 1),
                    ]);
                    $message = 'Cart updated successfully.';
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'variant_id' => $data['variant_id'],
                        'variant_option_id' => $variantOptionId,
                        'quantity' => $data['quantity'] ?? 1,
                        'unit_price' => $unitPrice,
                    ]);
                    $message = 'Item added to cart successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'cart' => $cart->fresh()->load('items.variant.product'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error adding to cart: ' . $e->getMessage(),
            ];
        }
    }

    public function updateCartItem(int $itemId, array $data): array
    {
        try {
            return DB::transaction(function () use ($itemId, $data) {
                $item = CartItem::findOrFail($itemId);
                $item->update([
                    'quantity' => $data['quantity'],
                ]);

                return [
                    'status' => 'success',
                    'message' => 'Cart item updated successfully.',
                    'cart' => $item->cart->fresh()->load('items.variant.product'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating cart item: ' . $e->getMessage(),
            ];
        }
    }

    public function removeCartItem(int $itemId): array
    {
        try {
            return DB::transaction(function () use ($itemId) {
                $item = CartItem::findOrFail($itemId);
                $cart = $item->cart;
                $item->delete();

                return [
                    'status' => 'success',
                    'message' => 'Item removed from cart successfully.',
                    'cart' => $cart->fresh()->load('items.variant.product'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error removing cart item: ' . $e->getMessage(),
            ];
        }
    }

    public function applyCoupon(int $cartId, string $couponCode): array
    {
        try {
            return DB::transaction(function () use ($cartId, $couponCode) {
                $cart = Cart::findOrFail($cartId);

                $coupon = Coupon::where('code', strtoupper($couponCode))
                    ->where('status', 'active')
                    ->first();

                if (!$coupon) {
                    return [
                        'status' => 'error',
                        'message' => 'Invalid coupon code.',
                    ];
                }

                if (!$coupon->isActive()) {
                    return [
                        'status' => 'error',
                        'message' => 'This coupon is no longer valid.',
                    ];
                }

                $cartTotal = $cart->items->sum(fn ($item) => $item->unit_price * $item->quantity);

                if ($cartTotal < $coupon->minimum_order_amount) {
                    return [
                        'status' => 'error',
                        'message' => 'Minimum order amount not met for this coupon.',
                    ];
                }

                return [
                    'status' => 'success',
                    'message' => 'Coupon applied successfully.',
                    'coupon' => $coupon,
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error applying coupon: ' . $e->getMessage(),
            ];
        }
    }

    public function removeCoupon(int $cartId): array
    {
        try {
            return DB::transaction(function () use ($cartId) {
                $cart = Cart::findOrFail($cartId);

                return [
                    'status' => 'success',
                    'message' => 'Coupon removed successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error removing coupon: ' . $e->getMessage(),
            ];
        }
    }

    public function syncCart(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $userId = $data['user_id'];
                $items = $data['items'] ?? [];
                
                // Get or create cart for user
                $cart = $this->getOrCreateCart($userId);
                
                // Use upsert to handle duplicates (update if exists, insert if not)
                $itemsToUpsert = [];
                foreach ($items as $itemData) {
                    $variant = \Modules\Catalog\Models\ProductVariant::findOrFail($itemData['variant_id']);
                    
                    // Calculate price with variant option adjustment
                    $unitPrice = $variant->sale_price;
                    $variantOptionId = $itemData['variant_option_id'] ?? null;
                    
                    if ($variantOptionId) {
                        $variantOption = \Modules\Catalog\Models\VariantOption::find($variantOptionId);
                        if ($variantOption && $variantOption->product_variant_id === $variant->id) {
                            $unitPrice += $variantOption->price_adjustment;
                        }
                    }
                    
                    $itemsToUpsert[] = [
                        'cart_id' => $cart->id,
                        'variant_id' => $itemData['variant_id'],
                        'variant_option_id' => $variantOptionId,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $unitPrice,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                // Upsert items (this will update existing or insert new)
                \Modules\Cart\Models\CartItem::upsert(
                    $itemsToUpsert,
                    ['cart_id', 'variant_id', 'variant_option_id'],
                    ['quantity', 'unit_price', 'updated_at']
                );
                
                return [
                    'status' => 'success',
                    'message' => 'Cart synced successfully.',
                    'cart' => $cart->fresh()->load('items.variant.product'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error syncing cart: ' . $e->getMessage(),
            ];
        }
    }
}
