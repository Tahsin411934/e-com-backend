import type { CartResponse, AddToCartPayload } from "@/types/cart";

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1";

/**
 * Get current user's cart.
 */
export async function getCart(): Promise<CartResponse> {
  const res = await fetch(`${API_BASE_URL}/my-cart`, {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
  return res.json();
}

/**
 * Add item to cart.
 */
export async function addToCartApi(payload: AddToCartPayload): Promise<CartResponse> {
  const res = await fetch(`${API_BASE_URL}/cart/add-item`, {
    method: "POST",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify(payload),
  });
  return res.json();
}

/**
 * Update cart item quantity.
 */
export async function updateCartItemApi(
  itemId: number,
  quantity: number
): Promise<CartResponse> {
  const res = await fetch(`${API_BASE_URL}/cart/update-item/${itemId}`, {
    method: "PUT",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ quantity }),
  });
  return res.json();
}

/**
 * Remove item from cart.
 */
export async function removeCartItemApi(itemId: number): Promise<CartResponse> {
  const res = await fetch(`${API_BASE_URL}/cart/remove-item/${itemId}`, {
    method: "DELETE",
    credentials: "include",
    headers: { Accept: "application/json" },
  });
  return res.json();
}

/**
 * Place order (checkout).
 */
export async function checkoutApi(data: {
  cart_id: number;
  shipping_address_id?: number;
  billing_address_id?: number;
  notes?: string;
  shipping_method?: string;
  payment_method?: string;
  delivery_address?: string;
  delivery_city?: string;
  delivery_phone?: string;
  delivery_notes?: string;
}): Promise<any> {
  const res = await fetch(`${API_BASE_URL}/checkout`, {
    method: "POST",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });
  return res.json();
}

/**
 * Sync local cart with backend (merge guest cart).
 */
export async function syncCartApi(items: Array<{ product_id: number; variant_id?: number; quantity: number }>): Promise<CartResponse> {
  const res = await fetch(`${API_BASE_URL}/carts/sync`, {
    method: "POST",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ items }),
  });
  return res.json();
}