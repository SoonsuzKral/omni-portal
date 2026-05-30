<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Search Console API
    |--------------------------------------------------------------------------
    */
    'gsc' => [
        'enabled' => env('GSC_ENABLED', false),
        'auth_type' => env('GSC_AUTH_TYPE', 'service_account'),
        'service_account_path' => env('GSC_SERVICE_ACCOUNT_PATH', storage_path('google-service-account.json')),
        'client_id' => env('GSC_CLIENT_ID'),
        'client_secret' => env('GSC_CLIENT_SECRET'),
        'refresh_token' => env('GSC_REFRESH_TOKEN'),
        'scopes' => [
            'https://www.googleapis.com/auth/webmasters.readonly',
        ],
        'site_url' => env('GSC_SITE_URL', env('SITE_URL', 'https://omviportal.com')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    */
    'sync' => [
        'days_back' => (int) env('GSC_SYNC_DAYS_BACK', 30),
        'batch_size' => (int) env('GSC_SYNC_BATCH_SIZE', 1000),
        'chunk_size' => (int) env('GSC_SYNC_CHUNK_SIZE', 200),
        'max_urls_per_request' => (int) env('GSC_MAX_URLS_PER_REQUEST', 5000),
        'rate_limit_per_minute' => (int) env('GSC_RATE_LIMIT', 60),
        'timeout_seconds' => (int) env('GSC_TIMEOUT', 120),
        'retry_attempts' => (int) env('GSC_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Adaptive Priority Weights
    |--------------------------------------------------------------------------
    */
    'weights' => [
        'gsc_indexed_bonus' => (float) env('TELEMETRY_WEIGHT_INDEXED_BONUS', 15.0),
        'gsc_high_ctr_boost' => (float) env('TELEMETRY_WEIGHT_CTR_BOOST', 10.0),
        'gsc_non_indexed_penalty' => (float) env('TELEMETRY_WEIGHT_NON_INDEXED_PENALTY', -20.0),
        'gsc_stale_penalty' => (float) env('TELEMETRY_WEIGHT_STALE_PENALTY', -10.0),
        'gsc_crawl_waste_penalty' => (float) env('TELEMETRY_WEIGHT_CRAWL_WASTE_PENALTY', -15.0),
        'gsc_decay_days' => (int) env('TELEMETRY_DECAY_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anomaly Detection Thresholds
    |--------------------------------------------------------------------------
    */
    'anomaly' => [
        'deindexing_drop_threshold' => (float) env('ANOMALY_DEINDEX_DROP', 50.0),
        'ctr_collapse_threshold' => (float) env('ANOMALY_CTR_COLLAPSE', 0.5),
        'ranking_volatility_threshold' => (float) env('ANOMALY_RANKING_VOLATILITY', 10.0),
        'crawl_drop_threshold' => (float) env('ANOMALY_CRAWL_DROP', 30.0),
        'sitemap_fetch_fail_threshold' => (int) env('ANOMALY_SITEMAP_FAIL', 3),
        'cooldown_hours' => (int) env('ANOMALY_COOLDOWN_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache & Queue
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'ttl_seconds' => (int) env('TELEMETRY_CACHE_TTL', 900),
        'prefix' => 'telemetry:',
    ],

    'queue' => [
        'connection' => env('TELEMETRY_QUEUE_CONNECTION', 'default'),
        'sync_queue' => env('TELEMETRY_SYNC_QUEUE', 'high'),
        'process_queue' => env('TELEMETRY_PROCESS_QUEUE', 'default'),
        'feedback_queue' => env('TELEMETRY_FEEDBACK_QUEUE', 'low'),
    ],
];
