import { api } from "@/lib/api";
import type { CategoryResponse, SingleCategoryResponse } from "@/types/category";

export const categoryService = {
  async getAll(): Promise<CategoryResponse> {
    return api<CategoryResponse>("/categories", {
      revalidate: 60,
      tags: ["categories"],
    });
  },

  async getBySlug(slug: string): Promise<SingleCategoryResponse> {
    return api<SingleCategoryResponse>(`/categories/${slug}`, {
      revalidate: 60,
      tags: [`category-${slug}`],
    });
  },
};