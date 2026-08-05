import { buildApiUrl } from "@/lib/api-url";

interface ApiOptions extends RequestInit {
  revalidate?: number;
  tags?: string[];
}

export async function api<T>(
  endpoint: string,
  options: ApiOptions = {}
): Promise<T> {
  const { revalidate, tags, ...fetchOptions } = options;

  const headers: Record<string, string> = {
    Accept: "application/json",
    "Content-Type": "application/json",
    ...(fetchOptions.headers as Record<string, string>),
  };

  const shouldBypassCache = revalidate === 0 || fetchOptions.cache === "no-store";
// console.log("api called with endpoint:", buildApiUrl(endpoint));
  const response = await fetch(buildApiUrl(endpoint), {
    ...fetchOptions,
    credentials: "include",
    headers,
    ...(shouldBypassCache
      ? { cache: "no-store" }
      : revalidate !== undefined
        ? { next: { revalidate, ...(tags ? { tags } : {}) } }
        : { cache: "no-store" }),
  });

  if (!response.ok) {
    const errorBody = await response.json().catch(() => ({}));
    throw new Error(errorBody.message || `API Error: ${response.status}`);
  }

  return await response.json();
}