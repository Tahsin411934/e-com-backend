<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;
use Modules\Cart\Services\CampaignPricingService;

class CheckoutController extends Controller
{
    public function __construct(private CampaignPricingService $campaignPricing)
    {
    }

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

                if (!$cart || $cart->items->isEmpty()) {
                    return [
                        'status' => 'error',
                        'message' => 'Cart is empty.',
                    ];
                }

                // Recalculate campaign prices on the server so an expired or changed
                // frontend/cart price can never be used for an order.
                foreach ($cart->items as $cartItem) {
                    $optionAdjustment = (float) ($cartItem->variantOption?->price_adjustment ?? 0);
                    $currentPrice = $this->campaignPricing->priceFor($cartItem->variant, $optionAdjustment)['price'];
                    if ((float) $cartItem->unit_price !== $currentPrice) {
                        $cartItem->update(['unit_price' => $currentPrice]);
                    }
                }
                $cart->load('items.variant', 'items.variantOption');

                // Calculate totals
                $subtotal = $cart->items->sum(fn($item) => $item->unit_price * $item->quantity);
                $discountTotal = 0; // TODO: Apply coupon logic if needed
                $taxTotal = 0; // TODO: Calculate tax if needed
                $shippingTotal = $subtotal >= 99 ? 0 : 10; // Free shipping over ৳99
                $grandTotal = $subtotal + $taxTotal + $shippingTotal - $discountTotal;

                // Create order
                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
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
                \Modules\Order\Models\Delivery::create([
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
                        $variantDescription .= ' - ' . $variantOption->color_name;
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $variant->product_id,
                        'variant_id' => $cartItem->variant_id,
                        'sku' => $variant->sku,
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
                'message' => 'Error placing order: ' . $e->getMessage(),
            ];
        }
    }
}
