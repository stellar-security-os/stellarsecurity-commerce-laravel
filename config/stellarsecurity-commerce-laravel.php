<?php

return [
    'base_url' => env('STELLAR_COMMERCE_CORE_BASE_URL', 'http://127.0.0.1:8000'),
    'api_prefix' => env('STELLAR_COMMERCE_CORE_API_PREFIX', '/api/v1'),

    'basic_auth' => [
        'enabled' => env('STELLAR_COMMERCE_CORE_BASIC_AUTH_ENABLED', true),
        'username' => env('STELLAR_COMMERCE_CORE_BASIC_AUTH_USER', ''),
        'password' => env('STELLAR_COMMERCE_CORE_BASIC_AUTH_PASS', ''),
    ],

    'timeout_seconds' => (int) env('STELLAR_COMMERCE_CORE_TIMEOUT_SECONDS', 10),
    'connect_timeout_seconds' => (int) env('STELLAR_COMMERCE_CORE_CONNECT_TIMEOUT_SECONDS', 5),

    'retry' => [
        'times' => (int) env('STELLAR_COMMERCE_CORE_RETRY_TIMES', 2),
        'sleep_ms' => (int) env('STELLAR_COMMERCE_CORE_RETRY_SLEEP_MS', 200),
    ],

    'headers' => [
        'User-Agent' => 'StellarSecurityCommerceLaravel/1.0.0',
        'Accept' => 'application/json',
    ],
];
