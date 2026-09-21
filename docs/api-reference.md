# AFT SOFT E-Commerce API Reference

Base URL: `https://admin.onehaatbd.com/api`

All JSON requests should send:

```http
Accept: application/json
Content-Type: application/json
```

## Authentication

Protected API requests use Laravel Sanctum:

```http
Authorization: Bearer <token>
```

Admin-only endpoints additionally require the authenticated user to have an
admin-panel role and the required permission. Storefront requests are scoped
from the `X-Store-Host` header and never trust a client-supplied `store_id`.

## Response format

Successful responses use the following shape:

```json
{
  "status": "success",
  "message": "Request completed successfully.",
  "data": {}
}
```

Validation errors return HTTP `422`:

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

Common status codes: `401` unauthenticated, `403` forbidden, `404` not found,
`409` conflict, `422` validation failure, and `429` rate limited.

## Authentication endpoints

| Method | Endpoint | Auth | Purpose |
| --- | --- | --- | --- |
| POST | `/v1/register` | Public | Register a customer |
| POST | `/v1/login` | Public | Create a Sanctum token |
| POST | `/v1/forgot-password` | Public | Request password reset |
| POST | `/v1/reset-password` | Public | Complete password reset |
| GET | `/v1/user` | Bearer | Return authenticated user |
| GET | `/v1/me` | Bearer | Return current user |
| POST | `/v1/logout` | Bearer | Revoke current token/session |
| POST | `/v1/logout-all` | Bearer | Revoke all user tokens |
| POST | `/v1/refresh` | Bearer | Refresh authentication token |
| POST | `/v1/change-password` | Bearer | Change password |
| GET | `/v1/customer-profile` | Bearer | Read customer profile |
| PUT | `/v1/customer-profile` | Bearer | Update customer profile |

Login and registration are rate-limited. Never store the returned token in a
URL or expose it in server logs.

## Store owner registration and plans

| Method | Endpoint | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/v1/register/store-slug-availability` | Public | Check requested slug |
| POST | `/v1/register/store-owner` | Public | Create owner, store and subscription |
| GET | `/v1/plans` | Public | List active plans available for signup |
| GET | `/v1/store` | Store owner/admin | Return current store |
| GET | `/v1/email/verify/{id}/{hash}` | Signed URL | Verify owner email |
| POST | `/v1/email/verification-notification` | Public | Resend verification email |

The store registration request must include the selected `plan_slug`. The
registration operation is transactional: user, store, subscription and
seeded demo data must either all succeed or all roll back.

Example:

```json
{
  "first_name": "Rahim",
  "last_name": "Uddin",
  "email": "rahim@example.com",
  "phone": "01711223344",
  "password": "secret1234",
  "password_confirmation": "secret1234",
  "store_name": "Rahim Electronics",
  "store_slug": "rahim-electronics",
  "plan_slug": "free-trial",
  "currency_code": "BDT",
  "timezone": "Asia/Dhaka"
}
```

## Store domain management

| Method | Endpoint | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/v1/store/domains` | Owner/admin | List store domains |
| POST | `/v1/store/domains` | Owner/admin | Add custom domain |
| POST | `/v1/store/domains/{id}/verify` | Owner/admin | Verify DNS records |
| POST | `/v1/store/domains/{id}/primary` | Owner/admin | Set primary domain |
| DELETE | `/v1/store/domains/{id}` | Owner/admin | Remove domain |

## Storefront tenant API

Base path: `/v1/storefront`

Every request must include the visitor's hostname:

```http
X-Store-Host: rahim-electronics.aftsoftandlimited.com
```

| Method | Endpoint | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/v1/storefront/resolve` | Public | Check whether a host has a store |
| GET | `/v1/storefront/settings` | Public | Store settings and branding |
| GET | `/v1/storefront/navbar-items` | Public | Store navigation |
| GET | `/v1/storefront/banners` | Public | Store banners |
| GET | `/v1/storefront/announcement-bars` | Public | Announcement bars |
| GET | `/v1/storefront/brands` | Public | Store brands |
| GET | `/v1/storefront/categories` | Public | Store categories |
| GET | `/v1/storefront/categories/{slug}` | Public | Category details |
| GET | `/v1/storefront/categories/{slug}/products` | Public | Category products |
| GET | `/v1/storefront/products/search?q=` | Public | Search products |
| GET | `/v1/storefront/products/{slug}` | Public | Product details and gallery |
| GET | `/v1/storefront/campaigns` | Public | Active campaigns |
| GET | `/v1/storefront/campaigns/{slug}` | Public | Campaign details |
| GET | `/v1/storefront/home/products-by-category` | Public | Homepage sections |
| GET | `/v1/storefront/subnavbar/{slug}/products` | Public | Sub-navigation products |
| POST | `/v1/storefront/product-requests` | Public | Submit product request |
| GET | `/v1/storefront/sitemap/products-count` | Public | Product sitemap count |
| GET | `/v1/storefront/sitemap/products` | Public | Product sitemap page |
| GET | `/v1/storefront/orders` | Customer bearer | Current customer's orders |
| GET | `/v1/storefront/orders/{id}` | Customer bearer | Customer order details |

If `/v1/storefront/resolve` returns `registered: false` with reason
`host_not_registered`, the frontend must show the store-not-found page and link
the visitor to `https://aftsoftandlimited.com/store-register`.

## Admin API resource groups

Admin API resources are protected by Sanctum plus role/permission middleware.
The standard REST methods are `GET index`, `POST store`, `GET show`, `PUT/PATCH
update`, and `DELETE destroy` unless noted otherwise.

| Resource group | Prefix |
| --- | --- |
| Users, roles, permissions, identities | `/v1/users`, `/v1/roles`, `/v1/permissions`, `/v1/identities` |
| Products, variants, categories, brands | `/v1/products`, `/v1/product-variants`, `/v1/categories`, `/v1/brands` |
| Orders, payments, refunds, deliveries | `/v1/orders`, `/v1/payments`, `/v1/refunds`, `/v1/deliveries` |
| Inventory, locations, movements | `/v1/inventories`, `/v1/inventory-locations`, `/v1/inventory-movements` |
| Suppliers and purchasing | `/v1/suppliers`, `/v1/purchase-orders`, `/v1/purchase-returns` |
| Shipping | `/v1/delivery-zones`, `/v1/delivery-drivers`, `/v1/shipments`, `/v1/shipment-events` |
| Campaigns and coupons | `/v1/campaigns`, `/v1/coupons` |
| Reviews and notifications | `/v1/product-reviews`, `/v1/notifications` |
| Frontend settings | `/v1/frontends`, `/v1/site-settings`, `/v1/banners` |
| Reports and audit logs | `/v1/reports`, `/v1/audit-logs` |

The browser admin panel should use the web routes where server-rendered pages
are provided, and the API routes for asynchronous tables/forms.

## Operational requirements

- Keep `APP_DEBUG=false` in production.
- Use HTTPS and `SESSION_SECURE_COOKIE=true`.
- Keep API tokens and SMTP credentials outside git and rotate exposed tokens.
- Run migrations with `php artisan migrate --force` during deployment.
- Run a persistent queue worker for queued mail and background jobs.
- Monitor `failed_jobs`, application errors and authentication failures.
- Keep this document updated when adding or changing an endpoint.

## Existing detailed references

- [Store owner registration API](store-owner-registration-api.md)
- [Storefront multi-tenant API](storefront-multi-tenant-api.md)
