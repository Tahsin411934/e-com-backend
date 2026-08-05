import { api } from "@/lib/api";
import type { ProductDetailResponse, ProductDetailData } from "@/types/product";

export const productDetailService = {
  async getBySlug(slug: string): Promise<ProductDetailData> {
    const res = await api<ProductDetailResponse>(`/products/${slug}`, {
      tags: [`product-${slug}`],
    });
    return res.data;
  },
};