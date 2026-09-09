<?php

namespace Modules\Cart\Services;

use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;

/**
 * Single source of truth for delivery-charge based shipping — shared by the
 * my-cart API and checkout so the /checkout screen and the placed order can
 * never disagree about shipping.
 */
class DeliveryChargeService
{
    /**
     * Shipping total for a cart — the highest product delivery charge in the
     * cart is taken ONCE per order (one parcel = one charge), regardless of
     * how many items or quantities it holds. An empty cart ships for ৳0.
     */
    public function shippingTotalFor(Cart $cart): float
    {
        $cart->loadMissing('items.variant.product');

        if ($cart->items->isEmpty()) {
            return 0.0;
        }

        return round((float) $cart->items->max(fn (CartItem $item) => $item->delivery_charge), 2);
    }
}
