<?php

use Rhapsody\Core\Support\Env;

/**
 * Rhapsody Framework Configuration
 *
 * Environment values are read via {@see Env}, which normalizes types and defaults.
 */

return [
    'logging'             => [
        // Configuration details resolve dynamically utilizing your system settings
        'php_error_log_path'    => 'C:\wamp\logs\php_error.log',
        'apache_error_log_path' => 'C:\wamp\logs\apache_error.log',
        'error_log_path'        => dirname(__DIR__) . '/storage/logs/errors.log',
    ],
    /**
     * The base URL of your application.
     */
    'base_url'            => Env::string('APP_URL') . Env::string('APP_BASE_URL', '/'),
    'app_name'            => Env::string('APP_NAME', 'Rhapsody Framework'),
    'app_env'             => Env::string('APP_ENV', 'production'),
    'app_version'         => '', // bootstrap will set this
    'theme'               => Env::string('APP_THEME', 'modern'),
    'cache'               => [
        'driver' => Env::string('CACHE_DRIVER', 'file'),
    ],
    'redis'               => [
        'host'     => Env::string('REDIS_HOST', '127.0.0.1'),
        'port'     => Env::int('REDIS_PORT', 6379),
        'password' => Env::get('REDIS_PASSWORD'),
    ],
    'database'            => [
        'host'     => Env::string('DB_HOST'),
        'port'     => Env::string('DB_PORT'),
        'dbname'   => Env::string('DB_NAME'),
        'user'     => Env::string('DB_USER'),
        'password' => Env::string('DB_PASS'),
        'driver'   => Env::string('DB_DRIVER', 'mysql'),
        'charset'  => 'utf8mb4',
    ],
    'mailer'              => [
        'transport'    => Env::string('MAIL_TRANSPORT', 'smtp'),
        'host'         => Env::string('MAIL_HOST', 'localhost'),
        'port'         => Env::int('MAIL_PORT', 2525),
        'username'     => Env::get('MAIL_USERNAME'),
        'password'     => Env::get('MAIL_PASSWORD'),
        'from_address' => Env::string('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'from_name'    => Env::string('MAIL_FROM_NAME', 'Example'),
    ],
    'middleware'          => [
        // Route-specific middleware (key => class)
        'map'    => [
            'admin' => \App\Middleware\AdminMiddleware::class,
            'auth'  => \Rhapsody\Core\Middleware\AuthMiddleware::class,
            'guest' => \Rhapsody\Core\Middleware\GuestMiddleware::class,
        ],
        // Global middleware (runs on every matched request)
        'global' => [
            \Rhapsody\Core\Middleware\VerifyCsrfTokenMiddleware::class,
            \Rhapsody\Core\Middleware\DdosMiddleware::class,
        ],
    ],
    // DDoS protection settings
    'ddos_enabled'        => Env::bool('DDOS_ENABLED', true),
    'ddos_max_requests'   => Env::int('DDOS_MAX_REQUESTS', 60),
    'ddos_time_window'    => Env::int('DDOS_TIME_WINDOW', 60),
    'ddos_block_duration' => Env::int('DDOS_BLOCK_DURATION', 300),
    'ddos_whitelist'      => explode(',', Env::string('DDOS_WHITELIST', '127.0.0.100,::100')),
    'ddos_blacklist'      => explode(',', Env::string('DDOS_BLACKLIST', '')),
    'lazy'                => [
        // Master toggle (overridden by env LAZY_LOADING_ENABLED)
        'enabled' => Env::bool('LAZY_LOADING_ENABLED', true),
        // List of services that should ALWAYS be instantiated eagerly.
        'eager'   => [
            // Add any user-defined services that should not be lazy.
        ],
    ],
    // Payment / Omnipay settings
    'payment'             => [
        // Which Omnipay gateway driver to use: 'Stripe' or 'PayPal_Rest'.
        // Overridden by PAYMENT_GATEWAY in .env.
        'gateway'  => Env::string('PAYMENT_GATEWAY', 'Stripe'),
        'currency' => Env::string('PAYMENT_CURRENCY', 'USD'),
    ],
    'ai'                  => [
        'provider' => Env::string('AI_PROVIDER', 'gemini'),
        'gemini'   => [
            'api_key'         => Env::string('GEMINI_API_KEY', ''),
            'model'           => Env::string('GEMINI_MODEL', 'gemini-2.5-flash'),
            'timeout'         => Env::int('GEMINI_TIMEOUT', 60),
            'connect_timeout' => Env::int('GEMINI_CONNECT_TIMEOUT', 5),
        ],
    ],
];
