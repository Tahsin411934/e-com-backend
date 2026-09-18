<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central platform hosts
    |--------------------------------------------------------------------------
    |
    | Requests addressed to these hosts carry no tenant context — they are
    | the central website and the API itself. Storefront requests must come
    | from a tenant host (wildcard subdomain or a verified custom domain).
    |
    */
    'central_domain' => env('STOREFRONT_CENTRAL_DOMAIN', 'onehaatbd.com'),
    'api_domain' => env('STOREFRONT_API_DOMAIN', 'admin.onehaatbd.com'),

    /*
    |--------------------------------------------------------------------------
    | Wildcard tenant domains
    |--------------------------------------------------------------------------
    |
    | Every store automatically gets {store_slug}.{domain_suffix} on
    | registration (e.g. rahim-electronics.shopio.test).
    |
    | Accepts a COMMA SEPARATED list so one backend can serve tenants on more
    | than one parent domain (e.g. "aftsoftandlimited.com,onehaatbd.com").
    | The FIRST entry is the primary suffix — it is the domain new stores get
    | on registration; the rest remain resolvable for existing tenants.
    |
    */
    'domain_suffix' => env('STOREFRONT_DOMAIN_SUFFIX', 'aftsoftandlimited.com'),

    /*
    |--------------------------------------------------------------------------
    | Additional platform hosts (never tenants)
    |--------------------------------------------------------------------------
    |
    | Comma separated list of hosts that must always render the central
    | website instead of a tenant storefront, in addition to central_domain
    | and api_domain above. Useful when the platform itself also runs on a
    | subdomain of a tenant suffix.
    |
    */
    'extra_platform_hosts' => env('STOREFRONT_EXTRA_PLATFORM_HOSTS'),

    /*
    |--------------------------------------------------------------------------
    | Server IP (custom-domain verification)
    |--------------------------------------------------------------------------
    |
    | When set, an owner's custom domain is verified if it resolves to this
    | IP via an A record. When null, verification happens through a CNAME
    | record pointing at the domain_suffix.
    |
    */
    'server_ip' => env('STOREFRONT_SERVER_IP'),

    /*
    |--------------------------------------------------------------------------
    | Reserved subdomains
    |--------------------------------------------------------------------------
    |
    | Store slugs are unique, but a slug colliding with an infrastructure
    | hostname (www, api, admin...) must never resolve to a tenant.
    |
    */
    'reserved_subdomains' => array_values(array_unique(array_merge([
        'www', 'api', 'admin', 'app', 'dashboard', 'mail', 'smtp', 'ftp',
        'cdn', 'static', 'assets', 'img', 'media', 'status', 'docs',
        'staging', 'dev', 'test', 'webmail', 'ns1', 'ns2', 'localhost',
    ], array_filter(array_map('trim', explode(',', (string) env('STOREFRONT_RESERVED_SUBDOMAINS', ''))))))),

    /*
    |--------------------------------------------------------------------------
    | Fallback store (local development / build-time rendering)
    |--------------------------------------------------------------------------
    |
    | When a request's host cannot be resolved to a tenant (localhost, the
    | Next.js dev server, or a build-time ISR render with no request
    | context), the storefront APIs can fall back to this store slug so
    | local development and `next build` keep working. Leave unset in
    | production — an unset value means unresolvable hosts are always a
    | hard 404.
    |
    */
    'fallback_store' => env('STOREFRONT_FALLBACK_STORE'),
];
