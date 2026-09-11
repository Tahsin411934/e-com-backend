<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Modules\Cart\Models\Cart;
use Modules\Cart\Services\CampaignPricingService;
use Modules\Cart\Services\DeliveryChargeService;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;
use Modules\Order\Models\Delivery;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;

class CheckoutController extends Controller
{
    public function __construct(
        private CampaignPricingService $campaignPricing,
        private DeliveryChargeService $deliveryCharges,
    ) {}

    /**
     * Group cart items by their product's store and build an array of
     * "store order groups". Each group becomes exactly one Order.
     *
     * Mixed carts are NOT blocked — they are split into one order per store
     * (Amazon-style). Delivery charge is the highest product charge INSIDE
     * each store group, so each store pays its own parcel charge.
     *
     * Returns:
     * [
     *   { store_id, items, subtotal, shipping_total, grand_total },
     *   ...
     * ]
     */
    private function splitCartByStore(Collection $items): array
    {
        $groups = [];

        foreach ($items as $item) {
            if (! $item->variant?->product) {
                continue;
            }

            $storeId = $item->variant->product->store_id;
            $key = $storeId === null ? 'platform' : (string) $storeId;

            $groups[$key] ??= [
                'store_id' => $storeId,
                'items' => collect(),
                'subtotal' => 0.0,
                'shipping_total' => 0.0,
                'grand_total' => 0.0,
            ];

            $groups[$key]['items'][] = $item;
            $groups[$key]['subtotal'] += $item->unit_price * $item->quantity;
        }

        $result = [];
        foreach ($groups as $group) {
            $group['subtotal'] = round($group['subtotal'], 2);
            $group['shipping_total'] = round(
                (float) $group['items']->max(fn ($it) => $it->delivery_charge),
                2
            );
            $group['grand_total'] = round($group['subtotal'] + $group['shipping_total'], 2);

            $result[] = $group;
        }

        return $result;
    }

    public function checkout(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $userId = auth()->id();

                // Get user's active cart
                $cart = Cart::where('user_id', $userId)
                    ->where('status', 'active')
                    ->with('items.variant.product', 'items.variantOption')
                    ->first();

                if (! $cart || $cart->items->isEmpty()) {
                    return [
                        'status' => 'error',
                        'message' => 'Cart is empty.',
                    ];
                }

                // Recalculate prices on the server using the shared pricing rule
                // (a live campaign with a discount > 0 wins, otherwise the
                // variant/option discount applies) so an expired campaign or a
                // stale cart price can never be used for an order.
                foreach ($cart->items as $cartItem) {
                    if (! $cartItem->variant) {
                        continue;
                    }

                    $currentPrice = $this->campaignPricing
                        ->finalPriceFor($cartItem->variant, $cartItem->variantOption)['unit_price'];

                    if ((float) $cartItem->unit_price !== $currentPrice) {
                        $cartItem->update(['unit_price' => $currentPrice]);
                    }
                }
                $cart->load('items.variant.product', 'items.variantOption');

                // Group cart items by store — one Order per store group.
                $groups = $this->splitCartByStore($cart->items);

                $orders = [];
                $totalSpend = 0.0;

                foreach ($groups as $group) {
                    $storeId = $group['store_id'];

                    $order = Order::create([
                        'order_number' => 'ORD-'.strtoupper(uniqid()),
                        'user_id' => $userId,
                        'store_id' => $storeId,
                        'source' => 'web',
                        'status' => 'pending',
                        'payment_status' => 'unpaid',
                        'fulfillment_status' => 'unfulfilled',
                        'currency_code' => 'BDT',
                        'subtotal' => $group['subtotal'],
                        'discount_total' => 0,
                        'tax_total' => 0,
                        'shipping_total' => $group['shipping_total'],
                        'grand_total' => $group['grand_total'],
                        'billing_address_id' => $request->billing_address_id,
                        'shipping_address_id' => $request->shipping_address_id,
                        'customer_note' => $request->notes,
                        'placed_at' => now(),
                    ]);

                    Delivery::create([
                        'order_id' => $order->id,
                        'user_id' => $userId,
                        'status' => 'pending',
                        'delivery_address' => $request->delivery_address ?? 'N/A',
                        'delivery_city' => $request->delivery_city ?? 'N/A',
                        'delivery_phone' => $request->delivery_phone ?? 'N/A',
                        'delivery_notes' => $request->notes,
                    ]);

                    foreach ($group['items'] as $cartItem) {
                        $variant = $cartItem->variant;
                        $variantOption = $cartItem->variantOption;

                        $productName = $variant->product->name ?? 'Unknown Product';
                        $variantName = $variant->name ?? 'Default';

                        // Build variant description with color if available
                        $variantDescription = $variantName;
                        if ($variantOption && $variantOption->color_name) {
                            $variantDescription .= ' - '.$variantOption->color_name;
                        }

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $variant->product_id,
                            'variant_id' => $cartItem->variant_id,
                            'sku' => $variantOption?->sku ?? $variant->sku,
                            'product_name' => $productName,
                            'variant_name' => $variantDescription,
                            'quantity' => $cartItem->quantity,
                            'unit_price' => $cartItem->unit_price,
                            'discount_total' => 0,
                            'tax_total' => 0,
                            'line_total' => $cartItem->unit_price * $cartItem->quantity,
                        ]);
                    }

                    $orders[] = $order->fresh()->load('items');
                    $totalSpend += $group['grand_total'];
                }

                // Clear the cart
                $cart->items()->delete();
                $cart->update(['status' => 'converted']);

                return [
                    'status' => 'success',
                    'message' => count($orders) > 1
                        ? count($orders).' orders placed successfully (one per store).'
                        : 'Order placed successfully.',
                    'orders' => $orders,
                    'order_count' => count($orders),
                    'total_amount' => round($totalSpend, 2),
                    'split_by_store' => count($orders) > 1,
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error placing order: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Guest (logged-out) checkout — no auth required.
     *
     * The frontend sends the cart items straight from localStorage together
     * with the customer's name and delivery details. Prices and shipping are
     * recalculated server-side with the exact same rules as the authed
     * checkout (live campaign > 0 wins, else variant/option discount; shipping
     * = the highest product delivery charge taken once per order).
     */
    public function guestCheckout(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'items' => 'required|array|min:1',
                    'items.*.product_id' => 'required|integer|exists:products,id',
                    'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
                    'items.*.variant_option_id' => 'nullable|integer|exists:variant_options,id',
                    'items.*.quantity' => 'required|integer|min:1|max:99',
                    'customer_name' => 'required|string|max:191',
                    'delivery_address' => 'required|string|max:191',
                    'delivery_city' => 'required|string|max:191',
                    'delivery_phone' => 'required|string|max:40',
                    'delivery_notes' => 'nullable|string|max:1000',
                ]);

                $orderItems = [];
                $subtotal = 0.0;
                $shippingTotal = 0.0;

                foreach ($request->input('items', []) as $itemData) {
                    $variant = ProductVariant::with('product')
                        ->when(
                            $itemData['variant_id'] ?? null,
                            fn ($query, $variantId) => $query->whereKey($variantId)
                        )
                        ->where('product_id', $itemData['product_id'])
                        ->first();

                    if (! $variant) {
                        return [
                            'status' => 'error',
                            'message' => 'The selected product variant is unavailable.',
                        ];
                    }

                    // Resolve the selected option (must belong to the variant)
                    $variantOptionId = $itemData['variant_option_id'] ?? null;
                    $variantOption = null;
                    if ($variantOptionId) {
                        $candidate = VariantOption::find($variantOptionId);
                        if ($candidate && $candidate->product_variant_id === $variant->id) {
                            $variantOption = $candidate;
                        }
                    }

                    // Shared pricing rule — never trust the client's price.
                    $unitPrice = $this->campaignPricing->finalPriceFor($variant, $variantOption)['unit_price'];
                    $quantity = (int) $itemData['quantity'];

                    $productName = $variant->product?->name ?? 'Unknown Product';
                    $variantName = $variant->name ?? 'Default';

                    // Build variant description with color if available
                    $variantDescription = $variantName;
                    if ($variantOption && $variantOption->color_name) {
                        $variantDescription .= ' - '.$variantOption->color_name;
                    }

                    // Group by the product's owning store (tenant isolation).
                    $storeId = $variant->product?->store_id;
                    $key = $storeId === null ? 'platform' : (string) $storeId;
                    $groups[$key] ??= [
                        'store_id' => $storeId,
                        'items' => [],
                        'subtotal' => 0.0,
                        'shipping_total' => 0.0,
                    ];
                    $groups[$key]['items'][] = [
                        'product_id' => $variant->product_id,
                        'variant_id' => $variant->id,
                        'sku' => $variantOption?->sku ?? $variant->sku,
                        'product_name' => $productName,
                        'variant_name' => $variantDescription,
                        'quantity' => $quantity,
                        'unit_price' => round($unitPrice, 4),
                        'line_total' => round($unitPrice * $quantity, 4),
                        'delivery_charge' => (float) ($variant->product?->delivery_charge ?? Product::DEFAULT_DELIVERY_CHARGE),
                    ];
                    $groups[$key]['subtotal'] += $unitPrice * $quantity;
                }

                $subtotal = round($subtotal, 2);
                $shippingTotal = round($shippingTotal, 2);

                // Create one order per store group.
                $orders = [];
                $totalSpend = 0.0;

                foreach ($groups as $group) {
                    $groupSubtotal = round($group['subtotal'], 2);
                    $groupShipping = round(
                        (float) collect($group['items'])->map(fn ($i) => $i['delivery_charge'])->max() ?? 0,
                        2
                    );

                    $order = Order::create([
                        'order_number' => 'ORD-'.strtoupper(uniqid()),
                        'user_id' => null,
                        'store_id' => $group['store_id'],
                        'source' => 'web',
                        'status' => 'pending',
                        'payment_status' => 'unpaid',
                        'fulfillment_status' => 'unfulfilled',
                        'currency_code' => 'BDT',
                        'subtotal' => $groupSubtotal,
                        'discount_total' => 0,
                        'tax_total' => 0,
                        'shipping_total' => $groupShipping,
                        'grand_total' => $groupSubtotal + $groupShipping,
                        'customer_name' => $request->customer_name,
                        'customer_note' => $request->delivery_notes,
                        'placed_at' => now(),
                    ]);

                    // Create delivery record (no user for guests)
                    Delivery::create([
                        'order_id' => $order->id,
                        'user_id' => null,
                        'status' => 'pending',
                        'delivery_address' => $request->delivery_address ?? 'N/A',
                        'delivery_city' => $request->delivery_city ?? 'N/A',
                        'delivery_phone' => $request->delivery_phone ?? 'N/A',
                        'delivery_notes' => $request->delivery_notes,
                    ]);

                    // Create order items
                    foreach ($group['items'] as $item) {
                        $item['order_id'] = $order->id;
                        OrderItem::create(collect($item)->except('delivery_charge')->toArray());
                    }

                    $orders[] = $order->fresh()->load('items');
                    $totalSpend += $groupSubtotal + $groupShipping;
                }

                return [
                    'status' => 'success',
                    'message' => count($orders) > 1
                        ? count($orders).' orders placed successfully (one per store).'
                        : 'Order placed successfully.',
                    'orders' => $orders,
                    'order_count' => count($orders),
                    'total_amount' => round($totalSpend, 2),
                    'split_by_store' => count($orders) > 1,
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error placing order: '.$e->getMessage(),
            ];
        }
    }
}
