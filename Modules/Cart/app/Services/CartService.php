<?php

namespace Modules\Cart\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Models\Coupon;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;
use Yajra\DataTables\DataTables;

class CartService
{
    public function __construct(protected CampaignPricingService $campaignPricing) {}

    public function getCartDataTable(Request $request)
    {
        $query = Cart::query()
            // items.variant.product is eager-loaded because every serialized
            // cart item carries its product delivery_charge ($appends) —
            // without it the admin carts table would lazy-load per row (N+1).
            ->with(['user', 'store', 'items.variant.product'])
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

    public function saveCart(array $data): JsonResponse
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

                return ApiResponse::success($cart->fresh()->load('items'), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving cart: '.$e->getMessage(), 500);
        }
    }

    public function getCartById(int $id): JsonResponse
    {
        try {
            $cart = Cart::with(['user', 'store', 'items'])->findOrFail($id);

            return ApiResponse::success($cart);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Cart not found.');
        }
    }

    public function deleteCart(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $cart = Cart::findOrFail($id);
                $cart->delete();

                return ApiResponse::success(null, 'Cart deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting cart: '.$e->getMessage(), 500);
        }
    }

    public function getOrCreateCart(int $userId, ?int $storeId = null): Cart
    {
        $cart = Cart::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (! $cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'store_id' => $storeId,
                'status' => 'active',
                'expires_at' => now()->addDays(7),
            ]);
        }

        return $cart;
    }

    /**
     * Recalculate every cart item's unit price using the shared pricing rule
     * (a live campaign with a discount > 0 wins, otherwise the variant-option /
     * variant discount applies) and persist any changed prices. This keeps old
     * carts — whose stored prices may pre-date a campaign or discount change —
     * consistent with what every other API would charge today.
     */
    public function refreshCartPrices(Cart $cart): void
    {
        $cart->load(['items.variant', 'items.variantOption']);

        foreach ($cart->items as $item) {
            if (! $item->variant) {
                continue;
            }

            $unitPrice = $this->campaignPricing->finalPriceFor($item->variant, $item->variantOption)['unit_price'];

            if ((float) $item->unit_price !== $unitPrice) {
                $item->update(['unit_price' => $unitPrice]);
            }
        }
    }

    /**
     * The user's active cart with fresh, rule-consistent prices — used by the
     * "my cart" API so the cart screen can never show a stale/stacked price.
     */
    public function freshPricedCart(int $userId): Cart
    {
        $cart = $this->getOrCreateCart($userId);
        $this->refreshCartPrices($cart);

        return $cart->fresh()->load('items.variant.product', 'items.variantOption');
    }

    public function addToCart(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $cart = $this->getOrCreateCart($data['user_id'], $data['store_id'] ?? null);

                // Find the variant - using direct class reference since import removed
                $variant = ProductVariant::findOrFail($data['variant_id']);

                // Resolve the selected variant option (must belong to the variant)
                $variantOptionId = $data['variant_option_id'] ?? null;
                $variantOption = null;
                if ($variantOptionId) {
                    $candidate = VariantOption::find($variantOptionId);
                    if ($candidate && $candidate->product_variant_id === $variant->id) {
                        $variantOption = $candidate;
                    }
                }

                // One shared pricing rule for every API: a LIVE campaign with a
                // discount greater than 0 wins (never stacked), otherwise the
                // variant-option discount applies (falling back to the parent
                // variant discount when the option has none).
                $unitPrice = $this->campaignPricing->finalPriceFor($variant, $variantOption)['unit_price'];

                // Check for existing item with same variant AND variant_option
                $existingItem = CartItem::where('cart_id', $cart->id)
                    ->where('variant_id', $data['variant_id'])
                    ->where('variant_option_id', $variantOptionId)
                    ->first();

                if ($existingItem) {
                    // Refresh the stored price too so items added before a
                    // campaign/discount change never keep a stale price.
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + ($data['quantity'] ?? 1),
                        'unit_price' => $unitPrice,
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

                // Make sure the returned cart never carries stale prices.
                $this->refreshCartPrices($cart);

                return ApiResponse::success($cart->fresh()->load('items.variant.product'), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error adding to cart: '.$e->getMessage(), 500);
        }
    }

    public function updateCartItem(int $itemId, array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($itemId, $data) {
                $item = CartItem::findOrFail($itemId);
                $item->update([
                    'quantity' => $data['quantity'],
                ]);

                // Keep prices in sync with the current campaign/discount state.
                $this->refreshCartPrices($item->cart);

                return ApiResponse::success($item->cart->fresh()->load('items.variant.product'), 'Cart item updated successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error updating cart item: '.$e->getMessage(), 500);
        }
    }

    public function removeCartItem(int $itemId): JsonResponse
    {
        try {
            return DB::transaction(function () use ($itemId) {
                $item = CartItem::findOrFail($itemId);
                $cart = $item->cart;
                $item->delete();

                // Keep the remaining items' prices in sync as well.
                $this->refreshCartPrices($cart);

                return ApiResponse::success($cart->fresh()->load('items.variant.product'), 'Item removed from cart successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error removing cart item: '.$e->getMessage(), 500);
        }
    }

    public function applyCoupon(int $cartId, string $couponCode): JsonResponse
    {
        try {
            return DB::transaction(function () use ($cartId, $couponCode) {
                $cart = Cart::findOrFail($cartId);

                $coupon = Coupon::where('code', strtoupper($couponCode))
                    ->where('status', 'active')
                    ->first();

                if (! $coupon) {
                    return ApiResponse::error('Invalid coupon code.', 500);
                }

                if (! $coupon->isActive()) {
                    return ApiResponse::error('This coupon is no longer valid.', 500);
                }

                $cartTotal = $cart->items->sum(fn ($item) => $item->unit_price * $item->quantity);

                if ($cartTotal < $coupon->minimum_order_amount) {
                    return ApiResponse::error('Minimum order amount not met for this coupon.', 500);
                }

                return ApiResponse::success($coupon, 'Coupon applied successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error applying coupon: '.$e->getMessage(), 500);
        }
    }

    public function removeCoupon(int $cartId): JsonResponse
    {
        try {
            return DB::transaction(function () use ($cartId) {
                $cart = Cart::findOrFail($cartId);

                return ApiResponse::success(null, 'Coupon removed successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error removing coupon: '.$e->getMessage(), 500);
        }
    }

    public function syncCart(array $data): JsonResponse
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
                    $variant = ProductVariant::findOrFail($itemData['variant_id']);

                    // Resolve the selected option (must belong to the variant)
                    $variantOptionId = $itemData['variant_option_id'] ?? null;
                    $variantOption = null;
                    if ($variantOptionId) {
                        $candidate = VariantOption::find($variantOptionId);
                        if ($candidate && $candidate->product_variant_id === $variant->id) {
                            $variantOption = $candidate;
                        }
                    }

                    // Shared pricing rule across all APIs: a LIVE campaign with a
                    // discount greater than 0 wins (never stacked), otherwise the
                    // variant-option discount applies (falling back to the parent
                    // variant discount when the option has none).
                    $unitPrice = $this->campaignPricing->finalPriceFor($variant, $variantOption)['unit_price'];

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
                CartItem::upsert(
                    $itemsToUpsert,
                    ['cart_id', 'variant_id', 'variant_option_id'],
                    ['quantity', 'unit_price', 'updated_at']
                );

                return ApiResponse::success($cart->fresh()->load('items.variant.product'), 'Cart synced successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error syncing cart: '.$e->getMessage(), 500);
        }
    }
}
