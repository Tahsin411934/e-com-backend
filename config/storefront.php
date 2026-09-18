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
    'central_domain' => env('STOREFRONT_CENTRAL_DOMAIN', 'shopio.test'),
    'api_domain' => env('STOREFRONT_API_DOMAIN', 'api.shopio.test'),

    /*
    |--------------------------------------------------------------------------
    | Wildcard tenant domain
    |--------------------------------------------------------------------------
    |
    | Every store automatically gets {store_slug}.{domain_suffix} on
    | registration (e.g. rahim-electronics.shopio.test).
    |
    */
    'domain_suffix' => env('STOREFRONT_DOMAIN_SUFFIX', env('STOREFRONT_CENTRAL_DOMAIN', 'shopio.test')),

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
    'reserved_subdomains' => [
        'www', 'api', 'admin', 'app', 'dashboard', 'mail', 'smtp', 'ftp',
        'cdn', 'static', 'assets', 'img', 'media', 'status', 'docs',
        'staging', 'dev', 'test', 'webmail', 'ns1', 'ns2', 'localhost',
    ],
];
