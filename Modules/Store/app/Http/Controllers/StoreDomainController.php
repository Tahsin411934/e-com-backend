<?php

namespace Modules\Store\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Store\Http\Requests\StoreDomainRequest;
use Modules\Store\Models\StoreDomain;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Support\StoreDomainResolver;

class StoreDomainController extends Controller
{
    /**
     * List the hostnames connected to the authenticated owner's store.
     * GET /api/v1/store/domains
     */
    public function index(): JsonResponse
    {
        $storeId = CurrentStore::id();

        if ($storeId === null) {
            return ApiResponse::notFound('No store is associated with your account.');
        }

        $domains = StoreDomain::query()
            ->where('store_id', $storeId)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get()
            ->map(fn (StoreDomain $domain) => $this->present($domain));

        return ApiResponse::success([
            'items' => $domains,
            'dns_instructions' => $this->dnsInstructions(),
        ], 'Store domains retrieved successfully.');
    }

    /**
     * Connect a new custom domain. POST /api/v1/store/domains
     */
    public function store(StoreDomainRequest $request): JsonResponse
    {
        $storeId = CurrentStore::id();

        if ($storeId === null) {
            return ApiResponse::notFound('No store is associated with your account.');
        }

        $domain = StoreDomain::create([
            'store_id' => $storeId,
            'domain' => $request->validated('domain'),
            'type' => 'custom',
            'is_primary' => false,
            'ssl_status' => 'pending',
            'verified_at' => null,
        ]);

        return ApiResponse::created([
            'domain' => $this->present($domain),
            'dns_instructions' => $this->dnsInstructions(),
        ], 'Domain added. Point it at the platform, then call the verify endpoint.');
    }

    /** Update the hostname of the store's single custom domain. */
    public function update(StoreDomainRequest $request, StoreDomain $storeDomain): JsonResponse
    {
        if ($response = $this->ensureOwnership($storeDomain)) {
            return $response;
        }

        if (! $storeDomain->isCustom()) {
            return ApiResponse::error('The free subdomain cannot be edited here.', 403);
        }

        $newDomain = $request->validated('domain');
        if ($newDomain !== $storeDomain->domain) {
            $storeDomain->update([
                'domain' => $newDomain,
                'verified_at' => null,
                'ssl_status' => 'pending',
            ]);
        }

        return ApiResponse::success([
            'domain' => $this->present($storeDomain->fresh()),
            'dns_instructions' => $this->dnsInstructions(),
        ], 'Custom domain updated. Update its DNS record, then verify it.');
    }

    /**
     * Verify DNS ownership. POST /api/v1/store/domains/{id}/verify
     */
    public function verify(StoreDomain $storeDomain): JsonResponse
    {
        if ($response = $this->ensureOwnership($storeDomain)) {
            return $response;
        }

        if ($storeDomain->isVerified()) {
            return ApiResponse::success($this->present($storeDomain), 'Domain is already verified.');
        }

        if (! $this->verifyDns($storeDomain->domain)) {
            return ApiResponse::error('DNS verification failed. Configure the record below and try again.', 422, [
                'dns_instructions' => $this->dnsInstructions(),
            ]);
        }

        $storeDomain->update([
            'verified_at' => now(),
            'ssl_status' => 'provisioning',
        ]);

        return ApiResponse::success(
            $this->present($storeDomain->fresh()),
            'Domain verified. SSL certificate is being provisioned.'
        );
    }

    // ─── remaining actions & helpers ───

    /**
     * Mark a domain as the store's primary hostname.
     * POST /api/v1/store/domains/{id}/primary
     */
    public function primary(StoreDomain $storeDomain): JsonResponse
    {
        if ($response = $this->ensureOwnership($storeDomain)) {
            return $response;
        }

        DB::transaction(function () use ($storeDomain) {
            StoreDomain::query()
                ->where('store_id', $storeDomain->store_id)
                ->whereKeyNot($storeDomain->getKey())
                ->update(['is_primary' => false]);

            $storeDomain->update(['is_primary' => true]);
        });

        return ApiResponse::success($this->present($storeDomain->fresh()), 'Primary domain updated.');
    }

    /**
     * Remove a custom domain. DELETE /api/v1/store/domains/{id}
     */
    public function destroy(StoreDomain $storeDomain): JsonResponse
    {
        if ($response = $this->ensureOwnership($storeDomain)) {
            return $response;
        }

        if (! $storeDomain->isCustom()) {
            return ApiResponse::error('The free subdomain cannot be removed.', 403);
        }

        $storeDomain->delete();

        return ApiResponse::success(null, 'Custom domain removed.');
    }

    /**
     * Domain rows are store-scoped: an owner may only touch their own.
     */
    private function ensureOwnership(StoreDomain $storeDomain): ?JsonResponse
    {
        if (CurrentStore::id() === null || (int) $storeDomain->store_id !== CurrentStore::id()) {
            return ApiResponse::notFound('Domain not found.');
        }

        return null;
    }

    /**
     * DNS ownership check: a CNAME record pointing at the platform suffix,
     * or — when a server IP is configured — an A record pointing at it.
     */
    private function verifyDns(string $domain): bool
    {
        $suffix = strtolower(StoreDomainResolver::primarySuffix());

        $records = @dns_get_record($domain, DNS_CNAME) ?: [];

        $verified = collect($records)->contains(
            fn (array $record) => str_ends_with(strtolower((string) ($record['target'] ?? '')), '.'.$suffix)
        );

        if (! $verified && ($serverIp = config('storefront.server_ip'))) {
            $aRecords = @dns_get_record($domain, DNS_A) ?: [];

            $verified = collect($aRecords)->contains(
                fn (array $record) => strtolower((string) ($record['ip'] ?? '')) === strtolower((string) $serverIp)
            );
        }

        return $verified;
    }

    private function present(StoreDomain $domain): array
    {
        return [
            'id' => $domain->id,
            'domain' => $domain->domain,
            'type' => $domain->type,
            'is_primary' => (bool) $domain->is_primary,
            'ssl_status' => $domain->ssl_status,
            'verified_at' => $domain->verified_at?->toIso8601String(),
        ];
    }

    private function dnsInstructions(): array
    {
        $suffix = (string) StoreDomainResolver::primarySuffix();
        $serverIp = config('storefront.server_ip');

        if ($serverIp) {
            return [
                'record' => 'A',
                'name' => '@',
                'value' => $serverIp,
                'alternative' => [
                    'record' => 'CNAME',
                    'name' => 'www',
                    'value' => $suffix,
                ],
            ];
        }

        return [
            'record' => 'CNAME',
            'name' => 'your-domain.com (or www)',
            'value' => $suffix,
        ];
    }
}

