import { api } from "@/lib/api";
import type { WishlistApiResponse, ToggleWishlistResponse, RemoveWishlistResponse } from "@/types/wishlist";

export async function getWishlist(): Promise<WishlistApiResponse> {
  return api<WishlistApiResponse>("/wishlists", {
    revalidate: 0,
  });
}

export async function toggleWishlist(productId: number): Promise<ToggleWishlistResponse> {
  return api<ToggleWishlistResponse>("/wishlists/toggle", {
    method: "POST",
    body: JSON.stringify({ product_id: productId }),
  });
}

export async function removeWishlistItem(productId: number): Promise<RemoveWishlistResponse> {
  return api<RemoveWishlistResponse>(`/wishlists/${productId}`, {
    method: "DELETE",
  });
}