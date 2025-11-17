<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dittofeed Write Key
    |--------------------------------------------------------------------------
    |
    | Your Dittofeed write key for authenticating requests to the Public API.
    | This is required for tracking events, identifying users, and page tracking.
    |
    */
    'write_key' => env('DITTOFEED_WRITE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Dittofeed Admin Key
    |--------------------------------------------------------------------------
    |
    | Your Dittofeed admin key for authenticating requests to the Admin API.
    | This is required for managing templates, segments, and other admin operations.
    |
    */
    'admin_key' => env('DITTOFEED_ADMIN_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Dittofeed Host
    |--------------------------------------------------------------------------
    |
    | The Dittofeed host URL. Use https://app.dittofeed.com for the cloud
    | version or your self-hosted URL.
    |
    */
    'host' => env('DITTOFEED_HOST', 'https://app.dittofeed.com'),

    /*
    |--------------------------------------------------------------------------
    | Workspace ID
    |--------------------------------------------------------------------------
    |
    | Your Dittofeed workspace ID. Required for admin API operations.
    |
    */
    'workspace_id' => env('DITTOFEED_WORKSPACE_ID'),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure whether to send events asynchronously via Laravel's queue system.
    | When enabled, events will be dispatched to the specified queue.
    |
    */
    'queue' => [
        'enabled' => env('DITTOFEED_QUEUE_ENABLED', false),
        'queue' => env('DITTOFEED_QUEUE_NAME', 'default'),
        'connection' => env('DITTOFEED_QUEUE_CONNECTION', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry behavior for failed API requests.
    |
    */
    'retry' => [
        'attempts' => env('DITTOFEED_RETRY_ATTEMPTS', 3),
        'delay' => env('DITTOFEED_RETRY_DELAY', 1000), // milliseconds
        'multiplier' => env('DITTOFEED_RETRY_MULTIPLIER', 2), // exponential backoff
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout Configuration
    |--------------------------------------------------------------------------
    |
    | Configure HTTP timeout for API requests (in seconds).
    |
    */
    'timeout' => env('DITTOFEED_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Auto Tracking Configuration
    |--------------------------------------------------------------------------
    |
    | Automatically track common events such as page views, authentication
    | events, and model events.
    |
    */
    'auto_track' => [
        'enabled' => env('DITTOFEED_AUTO_TRACK_ENABLED', true),
        'page_views' => env('DITTOFEED_AUTO_TRACK_PAGE_VIEWS', true),
        'auth_events' => env('DITTOFEED_AUTO_TRACK_AUTH_EVENTS', true),
        'model_events' => env('DITTOFEED_AUTO_TRACK_MODEL_EVENTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should not trigger automatic page view tracking.
    | Supports wildcards using Laravel's route pattern matching.
    |
    */
    'excluded_routes' => [
        'api/*',
        'admin/*',
        'telescope/*',
        'horizon/*',
        '_debugbar/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded URLs
    |--------------------------------------------------------------------------
    |
    | URL patterns that should not trigger automatic page view tracking.
    |
    */
    'excluded_urls' => [
        '*/health',
        '*/ping',
    ],

    /*
    |--------------------------------------------------------------------------
    | User ID Resolver
    |--------------------------------------------------------------------------
    |
    | A callable that resolves the current user ID. By default, it uses
    | Auth::id(). You can customize this to use a different field.
    |
    | Example: fn() => Auth::user()?->uuid
    |
    */
    'user_id_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Anonymous ID Cookie
    |--------------------------------------------------------------------------
    |
    | Configuration for the anonymous ID cookie used to track unauthenticated users.
    |
    */
    'anonymous_id' => [
        'cookie_name' => env('DITTOFEED_ANONYMOUS_ID_COOKIE', 'dittofeed_anonymous_id'),
        'cookie_lifetime' => env('DITTOFEED_ANONYMOUS_ID_LIFETIME', 525600), // 1 year in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for batch event processing.
    |
    */
    'batch' => [
        'size' => env('DITTOFEED_BATCH_SIZE', 100),
        'auto_flush' => env('DITTOFEED_BATCH_AUTO_FLUSH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Enrichment
    |--------------------------------------------------------------------------
    |
    | Automatically enrich events with contextual data such as IP address,
    | user agent, timezone, etc.
    |
    */
    'context' => [
        'enabled' => env('DITTOFEED_CONTEXT_ENABLED', true),
        'ip' => env('DITTOFEED_CONTEXT_IP', true),
        'user_agent' => env('DITTOFEED_CONTEXT_USER_AGENT', true),
        'timezone' => env('DITTOFEED_CONTEXT_TIMEZONE', true),
        'locale' => env('DITTOFEED_CONTEXT_LOCALE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug mode to log all API requests and responses.
    | WARNING: This may log sensitive data. Only enable in development.
    |
    */
    'debug' => env('DITTOFEED_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Testing Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, events will not be sent to Dittofeed.
    | Useful for testing without affecting production data.
    |
    */
    'testing' => env('DITTOFEED_TESTING', false),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Enable or disable SSL verification for API requests.
    | WARNING: Only disable in development. Never disable in production.
    |
    */
    'verify_ssl' => env('DITTOFEED_VERIFY_SSL', true),
];
