import { api } from "@/lib/api";
import type { ProductSearchResponse } from "@/types/product";

export const productService = {
  async search(
    query: string,
    categoryId?: number
  ): Promise<ProductSearchResponse> {
    const params = new URLSearchParams();
    params.set("q", query);
    if (categoryId !== undefined && categoryId > 0) {
      params.set("category_id", String(categoryId));
    }

    return api<ProductSearchResponse>(`/products/search?${params.toString()}`, {
      revalidate: 0,
    });
  },
};