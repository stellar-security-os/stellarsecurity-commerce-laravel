<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Commerce Core/Base API
    |--------------------------------------------------------------------------
    */
    'base_url' => env('STELLAR_COMMERCE_CORE_BASE_URL', 'http://127.0.0.1:8000'),
    'api_prefix' => env('STELLAR_COMMERCE_CORE_API_PREFIX', '/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Basic Auth (temporary protection)
    |--------------------------------------------------------------------------
    */
    'basic_auth' => [
        'enabled' => env('STELLAR_COMMERCE_CORE_BASIC_AUTH_ENABLED', true),
        'username' => env('STELLAR_COMMERCE_CORE_BASIC_AUTH_USER', ''),
        'password' => env('STELLAR_COMMERCE_CORE_BASIC_AUTH_PASS', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP behavior
    |--------------------------------------------------------------------------
    */
    'timeout_seconds' => (int) env('STELLAR_COMMERCE_CORE_TIMEOUT_SECONDS', 10),
    'connect_timeout_seconds' => (int) env('STELLAR_COMMERCE_CORE_CONNECT_TIMEOUT_SECONDS', 5),

    /*
    |--------------------------------------------------------------------------
    | Retry policy
    |--------------------------------------------------------------------------
    |
    | Note: POST /orders is safe to retry only if you pass an idempotency key.
    |
    */
    'retry' => [
        'times' => (int) env('STELLAR_COMMERCE_CORE_RETRY_TIMES', 2),
        'sleep_ms' => (int) env('STELLAR_COMMERCE_CORE_RETRY_SLEEP_MS', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'User-Agent' => 'StellarSecurityCommerceLaravel/1.0.0',
        'Accept' => 'application/json',
    ],
];
