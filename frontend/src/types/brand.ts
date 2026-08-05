export interface Brand {
  id: number;
  name: string;
  slug: string;
  logo: string | null;
}

export interface BrandsMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface BrandsRawResponse {
  success: boolean;
  message: string;
  data: Brand[];
  meta?: BrandsMeta;
}