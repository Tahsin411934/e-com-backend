<?php

namespace Modules\Store\Support;

use Modules\Store\Models\Store;
use Modules\Store\Models\StoreDomain;

class StoreDomainResolver
{
    private const CACHE_KEY = 'saas.storefront_store';

    /**
     * Store the current request is serving, resolved from the storefront
     * host. Memoized per request (same pattern as CurrentStore).
     */
    public static function store(): ?Store
    {
        if (app()->bound(self::CACHE_KEY)) {
            return app(self::CACHE_KEY);
        }

        $store = self::resolve();

        app()->instance(self::CACHE_KEY, $store);

        return $store;
    }

    /**
     * Forget the memoized store. Called at the start of every storefront
     * request (and by tests) so a resolution can never leak between
     * requests in long-running processes.
     */
    public static function reset(): void
    {
        if (app()->bound(self::CACHE_KEY)) {
            app()->forgetInstance(self::CACHE_KEY);
        }
    }

    /**
     * Normalized tenant host for the current request.
     *
     * The API lives on its own host (api.yourdomain.com), so the Next.js
     * server passes the visitor's original hostname through the
     * X-Store-Host header. The plain Host header is used as a fallback so
     * direct hits against a tenant host also resolve.
     */
    public static function host(): ?string
    {
        $request = request();

        $host = strtolower(trim((string) ($request->header('X-Store-Host') ?: $request->getHost())));
        $host = (string) preg_replace('/:\d+$/', '', $host);

        return $host !== '' ? $host : null;
    }

    /**
     * Absolute URL for a domain, scheme inherited from APP_URL.
     */
    public static function urlForDomain(string $domain): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme.'://'.strtolower($domain);
    }

    private static function resolve(): ?Store
    {
        $host = self::host();

        if ($host === null) {
            return null;
        }

        // Central platform hosts never carry tenant context.
        foreach (self::platformHosts() as $platformHost) {
            if ($host === $platformHost || ($platformHost !== '' && $host === 'www.'.$platformHost)) {
                return null;
            }
        }

        // 1) Wildcard subdomain: {store_slug}.{domain_suffix}
        $suffix = self::normalize((string) config('storefront.domain_suffix'));

        if ($suffix !== '' && str_ends_with($host, '.'.$suffix)) {
            $label = substr($host, 0, -\strlen($suffix) - 1);
            $slug = explode('.', $label)[0];

            if (in_array($slug, (array) config('storefront.reserved_subdomains', []), true)) {
                return null;
            }

            $store = Store::query()->where('slug', $slug)->first();

            return self::isActive($store) ? $store : null;
        }

        // 2) Custom domain — only DNS-verified rows are ever served.
        $domain = StoreDomain::query()
            ->where('domain', $host)
            ->where('type', 'custom')
            ->whereNotNull('verified_at')
            ->first();

        if ($domain === null) {
            return self::fallbackStore();
        }

        $store = $domain->store;

        return self::isActive($store) ? $store : null;
    }

    /**
     * Development convenience: when the host cannot be resolved (localhost,
     * Next.js dev server, build-time ISR) and STOREFRONT_FALLBACK_STORE is
     * configured, serve that store. Unset in production → strict 404.
     */
    private static function fallbackStore(): ?Store
    {
        $slug = trim((string) config('storefront.fallback_store'));

        if ($slug === '') {
            return null;
        }

        $store = Store::query()->where('slug', $slug)->first();

        return self::isActive($store) ? $store : null;
    }

    /**
     * @return list<string>
     */
    private static function platformHosts(): array
    {
        return array_values(array_filter(array_map(
            fn (mixed $host) => self::normalize((string) $host),
            [config('storefront.central_domain'), config('storefront.api_domain')],
        )));
    }

    private static function normalize(string $host): string
    {
        return strtolower(trim((string) preg_replace('/:\d+$/', '', $host)));
    }

    private static function isActive(?Store $store): bool
    {
        return $store !== null && $store->status === 'active';
    }
}
