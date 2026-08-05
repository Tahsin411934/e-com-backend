import { api } from "@/lib/api";
import type { CategorySortOption, CategoryProductsRawResponse, CategoryProductsData } from "@/types/product";
import { SORT_OPTIONS } from "@/types/product";

export { type CategorySortOption, type CategoryProductsData, SORT_OPTIONS };

export const categoryProductsService = {
  async getBySlug(
    slug: string,
    params?: {
      page?: number;
      per_page?: number;
      sort?: CategorySortOption;
      min_price?: number;
      max_price?: number;
      q?: string;
      brand_id?: number;
      refresh?: boolean;
    }
  ): Promise<CategoryProductsData> {
    const query = new URLSearchParams();

    if (params?.page) query.set("page", String(params.page));
    if (params?.per_page) {
      const clamped = Math.min(Math.max(1, params.per_page), 40);
      query.set("per_page", String(clamped));
    }
    if (params?.sort) query.set("sort", params.sort);
    if (params?.min_price !== undefined) query.set("min_price", String(params.min_price));
    if (params?.max_price !== undefined) query.set("max_price", String(params.max_price));
    if (params?.q) query.set("q", params.q);
    if (params?.brand_id !== undefined) query.set("brand_id", String(params.brand_id));
    if (params?.refresh) query.set("refresh", "1");

    const qs = query.toString();
    const endpoint = `/categories/${slug}/products${qs ? `?${qs}` : ""}`;

    const res = await api<CategoryProductsRawResponse>(endpoint, {
      revalidate: params?.refresh ? 0 : 0,
      tags: [`category-products-${slug}`],
    });

    return {
      category: res.data,
      products: res.products || [],
      meta: res.meta,
    };
  },
};