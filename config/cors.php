<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Here you may configure your settings for cross-origin resource sharing
| or "CORS". This determines what cross-origin operations may execute
| in web browsers. You are free to adjust these settings as needed.
|
| Allowed origins are built from:
|   1. CORS_ALLOWED_ORIGINS - comma separated list defined in your .env
|   2. FRONTEND_URL         - the frontend SPA origin (always merged in,
|                             so a missing CORS_ALLOWED_ORIGINS can never
|                             silently fall back to localhost again)
|
| In addition, a domain pattern allows any *.aftsoftandlimited.com
| subdomain (shopio, www.shopio, pos, admin, ...) plus localhost during
| development. That way a stale or empty env value can never hard-block
| your own sites.
|
| To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
|
*/

$allowedOrigins = array_values(array_unique(array_filter(array_map(
    'trim',
    array_merge(
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')),
        [(string) env('FRONTEND_URL', '')]
    )
), fn (string $origin): bool => $origin !== '')));

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins ?: ['http://localhost:3000'],

    'allowed_origins_patterns' => [
        '#^https?://([a-zA-Z0-9-]+\.)*aftsoftandlimited\.com$#i',
        '#^http://localhost(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Set-Cookie'],

    'max_age' => 0,

    'supports_credentials' => true,

];