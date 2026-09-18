# Storefront Multi-Tenant API

Same response shapes as the legacy `/api/v1` frontend APIs, but every request
is scoped to the store resolved from the **host**. The legacy APIs are
untouched and stay tenant-free.

## Endpoint

```
https://admin.onehaatbd.com/api/v1/storefront/...
```

The Next.js storefront must pass the visitor's hostname on every API call
(the API lives on its own host, so the original host can't be inferred):

```
X-Store-Host: rahim-electronics.onehaatbd.com   (wildcard subdomain)
X-Store-Host: myshop.com.bd                      (owner's custom domain)
```

## Tenant resolution rules (`ResolveStorefrontTenant`)

| Host | Result |
|---|---|
| `{slug}.{STOREFRONT_DOMAIN_SUFFIX}` | store matching `stores.slug` (active only) |
| verified `custom` row in `store_domains` | that store |
| central / api domain, `www.`-prefixed central, reserved subdomain (`www`, `api`, `admin`...) | 404 |
| unverified / unknown custom domain | 404 |

## Routes (all under `/api/v1/storefront`)

```
GET    navbar-items | navbar-items/{id} | navbar-items/{id}/children
GET    banners | banners/{id}
GET    announcement-bars | announcement-bars/{id}
GET    settings
GET    brands
GET    categories | categories/{slug} | categories/{slug}/products
GET    products/search?q=
GET    products/{slug}
GET    home/products-by-category
GET    subnavbar/{slug}/products
POST   product-requests            (store_id stamped from the host, never the payload)
GET    sitemap/products-count | sitemap/products?page=&limit=   (per-store cache)
GET    orders, orders/{order}      (auth:sanctum â€” scoped to user + tenant store)
```

Tenant not resolvable â†’ `404 { "status": "error", "message": "Store not found..." }`.

## Scoping convention

`Modules\Storefront\Support\StorefrontScope::apply($query)` â€” store rows +
global platform rows (`store_id IS NULL`). Settings: store row overrides the
global value per key. Sitemap cache keys are namespaced per store.

## Owner domain management (Store module, `/api/v1`)

```
GET    /v1/store/domains
POST   /v1/store/domains                { domain: "myshop.com.bd" }
POST   /v1/store/domains/{id}/verify    (CNAME â†’ suffix, or A â†’ STOREFRONT_SERVER_IP)
POST   /v1/store/domains/{id}/primary
DELETE /v1/store/domains/{id}
```

DNS record `CNAME myshop.com.bd â†’ onehaatbd.com` à¦¦à¦¿à¦²à§‡ verify à¦¹à¦¬à§‡à¥¤

## Config (`.env`)

```
STOREFRONT_CENTRAL_DOMAIN=onehaatbd.com
STOREFRONT_API_DOMAIN=admin.onehaatbd.com
STOREFRONT_DOMAIN_SUFFIX=onehaatbd.com      # wildcard *.onehaatbd.com
STOREFRONT_SERVER_IP=1.2.3.4                 # optional: A-record verification
```

## Local dev

Wildcard DNS à¦›à¦¾à¦¡à¦¼à¦¾ à¦Ÿà§‡à¦¸à§à¦Ÿ à¦•à¦°à¦¤à§‡ à¦¶à§à¦§à§ `X-Store-Host` header à¦ªà¦¾à¦ à¦¾à¦¨ (Postman/Insomnia),
à¦…à¦¥à¦¬à¦¾ `*.lvh.me` à¦¬à§à¦¯à¦¬à¦¹à¦¾à¦° à¦•à¦°à§à¦¨ (127.0.0.1 resolve à¦•à¦°à§‡)à¥¤

## Tests

```
php artisan test tests/Feature/Saas/StorefrontTenantApiTest.php
```

Covers: subdomain/custom-domain resolution, unverified + reserved + central +
unknown host rejection, tenant isolation (products/banners/sitemap), store
settings override, product-request stamping, legacy API untouched.
