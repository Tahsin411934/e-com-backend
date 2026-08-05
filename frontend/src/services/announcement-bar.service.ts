import { api } from "@/lib/api";
import type { AnnouncementBarResponse } from "@/types/announcement-bar";

export const announcementBarService = {
  async getAll(): Promise<AnnouncementBarResponse> {
    return api<AnnouncementBarResponse>("/announcement-bars", {
      revalidate: 60,
      tags: ["announcement-bar"],
    });
  },
};