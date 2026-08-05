import { api } from "@/lib/api";
import type { NavbarResponse } from "@/types/navbar";

export const navbarService = {
  async getAll(): Promise<NavbarResponse> {
    return api<NavbarResponse>("/navbar-items", {
      revalidate: 60,
      tags: ["navbar"],
    });
  },
};