<?php

$allowedOrigins = env('CORS_ALLOWED_ORIGINS');
$defaultOrigins = [];

if (env('APP_ENV', 'production') === 'local') {
    foreach ([
        env('CUSTOMER_STOREFRONT_URL', env('CUSTOMER_URL', env('FRONT_URL', 'http://localhost:3000/'))),
        env('VENDOR_ADMIN_URL', env('ADMIN_URL', 'http://localhost:3001/')),
    ] as $applicationUrl) {
        $parts = is_string($applicationUrl) ? parse_url($applicationUrl) : false;

        if (is_array($parts) && isset($parts['scheme'], $parts['host'])) {
            $defaultOrigins[] = $parts['scheme'] . '://' . $parts['host']
                . (isset($parts['port']) ? ':' . $parts['port'] : '');
        }
    }

    if ($defaultOrigins === []) {
        $defaultOrigins = ['http://localhost:3000', 'http://localhost:3001'];
    }
}

if (is_string($allowedOrigins)) {
    $allowedOrigins = array_values(array_filter(array_map('trim', explode(',', $allowedOrigins))));
}

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => is_array($allowedOrigins) ? $allowedOrigins : $defaultOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
