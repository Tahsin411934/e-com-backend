import { api } from "@/lib/api";
import type { Brand, BrandsMeta, BrandsRawResponse } from "@/types/brand";

export type BrandOption = Brand;

export const brandsService = {
  async getAll(params?: { search?: string; page?: number; limit?: number }): Promise<{ data: BrandOption[]; meta?: BrandsMeta }> {
    const query = new URLSearchParams();
    query.set("limit", String(params?.limit || 20));
    query.set("page", String(params?.page || 1));
    if (params?.search) query.set("q", params.search);

    const res = await api<BrandsRawResponse>(`/brands?${query.toString()}`, {
      cache: "no-store",
    });
    return { data: res.data || [], meta: res.meta };
  },
};