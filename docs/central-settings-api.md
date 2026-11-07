# Central marketplace settings

`GET /api/v1/central/settings` is a public, read-only endpoint in the Frontend module.
It requires no login or `X-Store-Host` header. Query parameters and tenant headers
cannot select a store: only settings with `store_id IS NULL` are returned.

The response is `{ "status": "success", "message": "Central settings retrieved successfully.", "data": { "site_name": "OneHaat.bd" } }`.
`data` is always an object, including when no settings exist. Image paths become
absolute URLs; null values remain null and soft-deleted settings are omitted.

The explicit public allowlist includes site name, logo, favicon, description,
primary color, Facebook/Twitter/Instagram/YouTube links, WhatsApp number, phone,
email, address, meta title/description, and browser-facing GTM enabled/id/container
URL/head/body fields. New settings and credentials are not exposed automatically.
Manage these values using the admin settings editor's Global (Platform) scope.

The existing `/api/v1/settings` and `/api/v1/storefront/settings` routes are unchanged.
The central Next.js frontend now uses this new endpoint through `centralFetch`.

Deploy the backend route/controller/service before the frontend change. No database
migration is needed. Rebuild the route cache during the normal backend deployment.

Regression tests: `php artisan test --filter=CentralSettingsTest`.
