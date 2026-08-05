import { api } from "@/lib/api";
import type { HomeApiResponse, HomeSection } from "@/types/home";

export const homeService = {
  async getHomePageData(params?: {
    limit_categories?: number;
    limit_products?: number;
  }): Promise<HomeSection[]> {
    const query = new URLSearchParams();
    if (params?.limit_categories) query.set("limit_categories", String(params.limit_categories));
    if (params?.limit_products) query.set("limit_products", String(params.limit_products));
    const qs = query.toString();
    console.log("homeService.getHomePageData: query", qs);
    const endpoint = `/home/products-by-category${qs ? `?${qs}` : ""}`;
    const res = await api<HomeApiResponse>(
      endpoint,
      {
        revalidate: 0,
        cache: "no-store",
        tags: ["home-page"],
      }
    );

    try {
      console.log("homeService.getHomePageData: response sample", JSON.stringify(res.data?.slice?.(0,1), null, 2));
    } catch (e) {
      // ignore logging failures
    }

    return res.data;
  },
};