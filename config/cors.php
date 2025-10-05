<?php

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

    // Apply CORS to API and Sanctum endpoints (and any other relevant public paths)
    'paths' => ['api/*', 'sanctum/*', 'assets/*', 'auth/*'],

    // Allow all HTTP methods by default
    'allowed_methods' => ['*'],

    // Use explicit origins to ensure ACAO is set on preflight when credentials are used.
    // FRONTEND_URL is preferred; falls back to APP_URL; finally '*' for development.
    'allowed_origins' => array_filter([
        env('FRONTEND_URL'),
        env('APP_URL'),
    ]) ?: ['*'],

    'allowed_origins_patterns' => [],

    // Allow any headers from the client
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    // Cache preflight response for 1 hour
    'max_age' => 3600,

    // Keep credentials support enabled (cookies/authorization headers).
    // NOTE: When using credentials, do not leave allowed_origins as only ['*'] in production.
    'supports_credentials' => true,

];
