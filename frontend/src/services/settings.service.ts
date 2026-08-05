import { api } from "@/lib/api";
import type { SettingsResponse } from "@/types/settings";

export const settingsService = {
  async getAll(): Promise<SettingsResponse> {
    return api<SettingsResponse>("/settings", 
      {
      revalidate: 3600,
      tags: ["settings"],
    }
  );
  },
};