import { api } from "@/lib/api";
import type { ReviewResponse } from "@/types/review";

export const reviewService = {
  /**
   * Get reviews for a product
   */
  async getProductReviews(productId: number): Promise<ReviewResponse> {
    return api<ReviewResponse>(`/products/${productId}/reviews`, {
      revalidate: 60,
    });
  },

  /**
   * Submit a new review
   */
  async submitReview(productId: number, reviewData: {
    rating: number;
    title: string;
    body: string;
  }): Promise<ReviewResponse> {
    return api<ReviewResponse>(`/products/${productId}/reviews`, {
      method: 'POST',
      body: JSON.stringify({
        product_id: productId,
        ...reviewData,
      }),
    });
  },
};