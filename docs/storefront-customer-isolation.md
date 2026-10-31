Storefront customer isolation
============================

Storefront requests resolve an active store from its wildcard or verified custom domain. Protected routes additionally require an active `Customer` belonging to that store. Admin `User` tokens cannot call customer endpoints; customer tokens cannot call legacy APIs that interpret IDs as `users.id`.

Customer carts, wishlists, orders and reviews use nullable `customer_id` foreign keys to `customers`. Legacy `user_id` ownership stays separate. Cart sync and both checkout flows validate product, variant and option relationships against the resolved store and calculate prices on the server. A storefront checkout creates one order for that store. Customer order history filters both customer and store.

Store settings contain only that store's own rows, with its registered name, email and phone as fallbacks. Global logos, contact details, social links, SEO and analytics code are not inherited. Search is tenant filtered before candidate limits and ranking. Campaign pricing must belong to the product's store. Unresolved tenants fail closed; the development fallback is disabled outside local/testing environments.

Deployment
----------

1. Deploy the backend and run `php artisan migrate --force` before deploying the corresponding frontend.
2. Deploy the frontend with the same API configuration. API URLs may specify the backend origin, `/api`, or `/api/v1`; requests normalize to the versioned API.
3. Verify on two different stores: customer login, cart sync, guest/customer checkout, wishlist, reviews, password reset and order history. Repeat private requests using the other store's token and IDs; these must fail.

Historical `user_id` records are deliberately not reassigned by matching numeric customer IDs. An ID match cannot prove ownership. Any historical reconciliation needs independently verified customer/store ownership. Existing records are retained.

Frontend cart persistence uses the resolved store ID. Unscoped legacy carts are not automatically imported. Authenticated wishlists are fetched from the API rather than restored from a previous account's local storage; session changes also invalidate pending wishlist responses.

Verification
------------

Backend regression coverage: `php artisan test --filter=StorefrontCustomerIsolationTest`.

Frontend isolation checks (in `ecomarce-frontend`): `node --test tests/storefront-isolation.test.cjs`.

Local verification: 13 frontend checks pass, changed PHP files pass `php -l`, and application TypeScript semantic checks pass. Full Laravel execution is currently blocked by unreadable installed vendor files (`symfony/polyfill-uuid/bootstrap.php`). The standard frontend type-check command is blocked by a truncated installed `@types/node/process.d.ts`; generated `.next/dev/types/validator.ts` also references an absent old route. These dependency/generated-file failures must be resolved before claiming a full integration/build pass. No production migration has been run from this workspace.
