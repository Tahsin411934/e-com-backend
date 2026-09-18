<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Store\Support\StoreDomainResolver;

/**
 * Tenant discovery endpoint for the storefront proxy (Next.js middleware).
 *
 * Sits OUTSIDE the `storefront.tenant` group on purpose: its job is to answer
 * "is a store registered on this host?" — including for hosts that are NOT
 * registered. The middleware group would hard-404 those hosts, which is
 * exactly the signal this endpoint must deliver structurally instead.
 *
 * Responses (HTTP 200 in every case, the answer is in `data.registered`):
 *   - registered: true   → { registered, host, store: {id, name, slug} }
 *   - registered: false  → { registered, host, reason: host_not_registered }
 *                          (a storefront subdomain, but no active store)
 *   - registered: false  → { registered, host, reason: reserved_subdomain }
 *                          (infrastructure hostname: www, api, admin...)
 *   - registered: false  → { registered, host, reason: not_a_storefront_host }
 *                          (central site, localhost, unrelated domains —
 *                          the proxy renders them normally)
 *   - registered: false  → { registered, host: null, reason: no_host }
 */
class StoreResolveController extends Controller
{
    public function show(): JsonResponse
    {
        $host = StoreDomainResolver::host();

        if ($host === null || $host === '') {
            return ApiResponse::success([
                'registered' => false,
                'host' => null,
                'reason' => 'no_host',
            ], 'No storefront host on the request.');
        }

        if (! StoreDomainResolver::isStorefrontHost($host)) {
            return ApiResponse::success([
                'registered' => false,
                'host' => $host,
                'reason' => 'not_a_storefront_host',
            ], 'Host is not a tenant storefront domain.');
        }

        $reserved = StoreDomainResolver::reservedLabel($host);

        if ($reserved !== null) {
            return ApiResponse::success([
                'registered' => false,
                'host' => $host,
                'reason' => 'reserved_subdomain',
            ], 'The "'.$reserved.'" subdomain is reserved for the platform.');
        }

        $store = StoreDomainResolver::resolveForHost($host);

        if ($store === null) {
            return ApiResponse::success([
                'registered' => false,
                'host' => $host,
                'reason' => 'host_not_registered',
            ], 'No store is registered on this subdomain.');
        }

        return ApiResponse::success([
            'registered' => true,
            'host' => $host,
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
            ],
        ], 'Store resolved for host.');
    }
}
