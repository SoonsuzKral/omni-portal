<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Panel IP Whitelist
    |--------------------------------------------------------------------------
    |
    | Restrict admin panel access to specific IP addresses.
    | Leave empty to allow all IPs.
    | Supports: single IPs, CIDR notation (192.168.1.0/24), ranges (192.168.1.1-192.168.1.254)
    |
    | Example: ['192.168.1.100', '10.0.0.0/24', '172.16.0.1-172.16.0.50']
    |
    */
    'admin_whitelist' => env('ADMIN_WHITELIST', ''),

    /*
    |--------------------------------------------------------------------------
    | API IP Whitelist
    |--------------------------------------------------------------------------
    |
    | Restrict API access to specific IP addresses.
    | Leave empty to allow all IPs.
    |
    */
    'api_whitelist' => env('API_WHITELIST', ''),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Settings
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'api' => [
            'max_attempts' => 60,
            'decay_seconds' => 60,
            'block_duration' => 300,
        ],
        'auth' => [
            'max_attempts' => 5,
            'decay_seconds' => 300,
            'block_duration' => 900,
        ],
        'login' => [
            'max_attempts' => 5,
            'decay_seconds' => 300,
            'block_duration' => 900,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SQL Injection Protection
    |--------------------------------------------------------------------------
    */
    'sql_injection' => [
        'enabled' => true,
        'log_attempts' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS Protection
    |--------------------------------------------------------------------------
    */
    'xss' => [
        'enabled' => true,
        'allowed_tags' => ['p', 'br', 'b', 'i', 'u', 'em', 'strong', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'img', 'div', 'span', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    */
    'csrf' => [
        'enabled' => true,
        'except_routes' => [
            'api/*',
            'sitemap.xml',
            'sitemap-*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Token Expiry
    |--------------------------------------------------------------------------
    */
    'api_token' => [
        'expiry_days' => 365,
        'max_tokens_per_user' => 10,
    ],
];