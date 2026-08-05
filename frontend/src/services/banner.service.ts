import { api } from "@/lib/api";
import type { BannerResponse } from "@/types/banner";

export const bannerService = {
  async getAll(): Promise<BannerResponse> {
    return api<BannerResponse>("/banners", {
      revalidate: 60,
      tags: ["banners"],
    });
  },
};