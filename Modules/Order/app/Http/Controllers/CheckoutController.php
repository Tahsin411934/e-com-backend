<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function checkout(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $userId = auth()->id();

                // Get user's active cart
                $cart = Cart::where('user_id', $userId)
                    ->where('status', 'active')
                    ->with('items.variant', 'items.variantOption')
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
                $cart->load('items.variant', 'items.variantOption');

                // Calculate totals
                $subtotal = $cart->items->sum(fn ($item) => $item->unit_price * $item->quantity);
                $discountTotal = 0; // TODO: Apply coupon logic if needed
                $taxTotal = 0; // TODO: Calculate tax if needed
                // Shipping = the highest product delivery charge in the cart,
                // taken ONCE per order (one parcel = one charge). Every product
                // carries an editable delivery_charge (admin), default ৳120.
                $shippingTotal = $this->deliveryCharges->shippingTotalFor($cart);
                $grandTotal = $subtotal + $taxTotal + $shippingTotal - $discountTotal;

                // Create order
                $order = Order::create([
                    'order_number' => 'ORD-'.strtoupper(uniqid()),
                    'user_id' => $userId,
                    'store_id' => $cart->store_id,
                    'source' => 'web',
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'fulfillment_status' => 'unfulfilled',
                    'currency_code' => 'BDT',
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'tax_total' => $taxTotal,
                    'shipping_total' => $shippingTotal,
                    'grand_total' => $grandTotal,
                    'billing_address_id' => $request->billing_address_id,
                    'shipping_address_id' => $request->shipping_address_id,
                    'customer_note' => $request->notes,
                    'placed_at' => now(),
                ]);

                // Create delivery record
                Delivery::create([
                    'order_id' => $order->id,
                    'user_id' => $userId,
                    'status' => 'pending',
                    'delivery_address' => $request->delivery_address ?? 'N/A',
                    'delivery_city' => $request->delivery_city ?? 'N/A',
                    'delivery_phone' => $request->delivery_phone ?? 'N/A',
                    'delivery_notes' => $request->delivery_notes,
                ]);

                // Create order items from cart items
                foreach ($cart->items as $cartItem) {
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

                // Clear the cart
                $cart->items()->delete();
                $cart->update(['status' => 'converted']);

                return [
                    'status' => 'success',
                    'message' => 'Order placed successfully.',
                    'order' => $order->load('items'),
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
                    'items.*.variant_id' => 'required|integer|exists:product_variants,id',
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
                    $variant = ProductVariant::with('product')->findOrFail($itemData['variant_id']);

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

                    // Shipping = the highest product delivery charge in the
                    // order, taken ONCE (one parcel = one charge).
                    $deliveryCharge = (float) ($variant->product?->delivery_charge ?? Product::DEFAULT_DELIVERY_CHARGE);
                    if ($deliveryCharge > $shippingTotal) {
                        $shippingTotal = $deliveryCharge;
                    }

                    $productName = $variant->product?->name ?? 'Unknown Product';
                    $variantName = $variant->name ?? 'Default';

                    // Build variant description with color if available
                    $variantDescription = $variantName;
                    if ($variantOption && $variantOption->color_name) {
                        $variantDescription .= ' - '.$variantOption->color_name;
                    }

                    $orderItems[] = [
                        'product_id' => $variant->product_id,
                        'variant_id' => $variant->id,
                        'sku' => $variantOption?->sku ?? $variant->sku,
                        'product_name' => $productName,
                        'variant_name' => $variantDescription,
                        'quantity' => $quantity,
                        'unit_price' => round($unitPrice, 4),
                        'line_total' => round($unitPrice * $quantity, 4),
                    ];

                    $subtotal += $unitPrice * $quantity;
                }

                $subtotal = round($subtotal, 2);
                $shippingTotal = round($shippingTotal, 2);

                // Create order (no user — guest checkout)
                $order = Order::create([
                    'order_number' => 'ORD-'.strtoupper(uniqid()),
                    'user_id' => null,
                    'store_id' => null,
                    'source' => 'web',
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'fulfillment_status' => 'unfulfilled',
                    'currency_code' => 'BDT',
                    'subtotal' => $subtotal,
                    'discount_total' => 0,
                    'tax_total' => 0,
                    'shipping_total' => $shippingTotal,
                    'grand_total' => $subtotal + $shippingTotal,
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
                foreach ($orderItems as $item) {
                    $item['order_id'] = $order->id;
                    OrderItem::create($item);
                }

                return [
                    'status' => 'success',
                    'message' => 'Order placed successfully.',
                    'order' => $order->load('items'),
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
