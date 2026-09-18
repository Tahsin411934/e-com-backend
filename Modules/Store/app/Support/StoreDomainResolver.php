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

    /**
     * Resolve a store for an explicit host (defaults to the current request
     * host). Public so discovery endpoints (GET /v1/storefront/resolve) can
     * ask "is this host registered?" without the tenant middleware's hard
     * 404 — an unregistered host is an expected answer, not an error.
     */
    public static function resolveForHost(?string $host = null): ?Store
    {
        $host = self::normalize((string) ($host ?? self::host() ?? ''));

        if ($host === '') {
            return null;
        }

        // Central platform hosts never carry tenant context.
        foreach (self::platformHosts() as $platformHost) {
            if ($host === $platformHost || ($platformHost !== '' && $host === 'www.'.$platformHost)) {
                return null;
            }
        }

        // 1) Wildcard subdomains: {store_slug}.{domain_suffix}. Suffixes are
        //    longest-first, so overlapping suffixes resolve deterministically.
        foreach (self::suffixes() as $suffix) {
            if ($suffix === '' || ! str_ends_with($host, '.'.$suffix)) {
                continue;
            }

            $label = substr($host, 0, -\strlen($suffix) - 1);
            $slug = explode('.', $label)[0];

            if (in_array($slug, self::reservedSubdomains(), true)) {
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

    private static function resolve(): ?Store
    {
        return self::resolveForHost(self::host());
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
     * Wildcard storefront suffixes (longest first). `STOREFRONT_DOMAIN_SUFFIX`
     * accepts a comma separated list so one backend can serve tenants on more
     * than one parent domain; the first entry is the primary suffix new
     * stores are registered on.
     *
     * @return list<string>
     */
    public static function suffixes(): array
    {
        $raw = config('storefront.domain_suffix');
        $raw = is_array($raw) ? $raw : explode(',', (string) $raw);

        $suffixes = array_values(array_filter(array_map(
            fn (mixed $suffix) => self::normalize((string) $suffix),
            $raw,
        )));

        usort($suffixes, fn (string $a, string $b) => \strlen($b) <=> \strlen($a));

        return array_values(array_unique($suffixes));
    }

    /**
     * Primary wildcard suffix — the suffix new stores get on registration.
     */
    public static function primarySuffix(): string
    {
        return self::suffixes()[0] ?? '';
    }

    /**
     * True when the host looks like a tenant storefront: it sits under one of
     * the configured wildcard suffixes and is not a platform (central/api)
     * host. Unknown domains (localhost, preview hosts, unrelated domains)
     * return false so callers can render normally instead of blocking.
     */
    public static function isStorefrontHost(?string $host = null): bool
    {
        $host = self::normalize((string) ($host ?? self::host() ?? ''));

        if ($host === '') {
            return false;
        }

        foreach (self::platformHosts() as $platformHost) {
            if ($host === $platformHost || ($platformHost !== '' && $host === 'www.'.$platformHost)) {
                return false;
            }
        }

        foreach (self::suffixes() as $suffix) {
            if ($suffix !== '' && str_ends_with($host, '.'.$suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The reserved subdomain label this host maps to (`www.foo.bar` → `www`),
     * or null when the host is not a reserved infrastructure hostname.
     */
    public static function reservedLabel(?string $host = null): ?string
    {
        $host = self::normalize((string) ($host ?? self::host() ?? ''));

        foreach (self::suffixes() as $suffix) {
            if ($suffix === '' || ! str_ends_with($host, '.'.$suffix)) {
                continue;
            }

            $slug = explode('.', substr($host, 0, -\strlen($suffix) - 1))[0];

            return in_array($slug, self::reservedSubdomains(), true) ? $slug : null;
        }

        return null;
    }

    /**
     * Platform (central website / API) hosts — never tenants.
     *
     * @return list<string>
     */
    private static function platformHosts(): array
    {
        $raw = config('storefront.extra_platform_hosts');
        $extra = is_array($raw) ? $raw : ($raw !== null ? explode(',', (string) $raw) : []);

        $hosts = array_merge(
            [config('storefront.central_domain'), config('storefront.api_domain')],
            $extra,
        );

        return array_values(array_filter(array_map(
            fn (mixed $host) => self::normalize((string) $host),
            $hosts,
        )));
    }

    /**
     * Reserved infrastructure subdomains (www, api, admin...). A store slug
     * colliding with one of these must never resolve to a tenant.
     *
     * @return list<string>
     */
    private static function reservedSubdomains(): array
    {
        return array_values(array_filter(array_map(
            fn (mixed $subdomain) => self::normalize((string) $subdomain),
            (array) config('storefront.reserved_subdomains', []),
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
