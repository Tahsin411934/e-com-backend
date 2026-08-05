import { api } from "@/lib/api";
import type { SubnavbarProductsResponse, SubnavbarProductsData, SubnavbarSortOption } from "@/types/subnavbar";

export const subnavbarService = {
  async getProducts(
    slug: string,
    params?: {
      page?: number;
      per_page?: number;
      sort?: SubnavbarSortOption;
    }
  ): Promise<SubnavbarProductsData> {
    const query = new URLSearchParams();

    if (params?.page) query.set("page", String(params.page));
    if (params?.per_page) {
      const clamped = Math.min(Math.max(1, params.per_page), 40);
      query.set("per_page", String(clamped));
    }
    if (params?.sort) query.set("sort", params.sort);

    const qs = query.toString();
    const endpoint = `/subnavbar/${slug}/products${qs ? `?${qs}` : ""}`;

    const res = await api<SubnavbarProductsResponse>(endpoint, {
      revalidate: 120,
      tags: [`subnavbar-products-${slug}`],
    });

    return res.data;
  },
};