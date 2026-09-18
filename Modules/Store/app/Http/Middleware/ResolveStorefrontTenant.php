<?php

namespace Modules\Store\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Support\StoreDomainResolver;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the storefront tenant from the request host and binds it as the
 * CurrentStore for the remainder of the request.
 *
 * Applied only to the storefront routes (Modules\Storefront) — the legacy
 * /api/v1 frontend & admin APIs stay tenant-free and keep working as before.
 */
class ResolveStorefrontTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Always resolve fresh for this request — the memoized value would
        // otherwise survive across requests inside a long-running worker.
        StoreDomainResolver::reset();

        $store = StoreDomainResolver::store();

        if ($store === null) {
            return ApiResponse::notFound(
                'Store not found. Send the storefront host via the X-Store-Host header (or request a valid tenant domain).'
            );
        }

        // Tenant context always wins — even an authenticated store owner
        // browsing another storefront sees that storefront's data.
        CurrentStore::set($store->id);

        return $next($request);
    }
}
