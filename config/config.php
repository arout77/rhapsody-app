<?php

/**
 * Rhapsody Framework Configuration
 *
 * This file reads settings directly from the $_ENV superglobal,
 * which is reliably populated by the Dotenv library.
 */

return [
    'logging'             => [
        // Configuration details resolve dynamically utilizing your system settings
        'php_error_log_path'    => 'C:\MAMP\logs\php_error.log',
        'apache_error_log_path' => 'C:\MAMP\logs\apache_error.log',
        'error_log_path'        => dirname(__DIR__) . '/storage/logs/errors.log',
    ],
    /**
     * The base URL of your application.
     */
    'base_url'            => $_ENV['APP_BASE_URL'] ?? '/',
    'app_env'             => $_ENV['APP_ENV'] ?? 'production',
    'app_version'         => 'v1.4.6',
    'theme'               => $_ENV['APP_THEME'] ?? 'default',
    'cache'               => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
    ],
    'redis'               => [
        'host'     => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port'     => $_ENV['REDIS_PORT'] ?? 6379,
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
    ],
    'database'            => [
        'host'     => $_ENV['DB_HOST'],
        'port'     => $_ENV['DB_PORT'],
        'dbname'   => $_ENV['DB_NAME'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'charset'  => 'utf8mb4',
    ],
    'mailer'              => [
        'transport'    => $_ENV['MAIL_TRANSPORT'] ?? 'smtp',
        'host'         => $_ENV['MAIL_HOST'] ?? 'localhost',
        'port'         => $_ENV['MAIL_PORT'] ?? 2525,
        'username'     => $_ENV['MAIL_USERNAME'] ?? null,
        'password'     => $_ENV['MAIL_PASSWORD'] ?? null,
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'hello@example.com',
        'from_name'    => $_ENV['MAIL_FROM_NAME'] ?? 'Example',
    ],
    'middleware'          => [
        // Route-specific middleware (key => class)
        'map'    => [
            'auth'    => \Rhapsody\Core\Middleware\AuthMiddleware::class,
            'guest'   => \Rhapsody\Core\Middleware\GuestMiddleware::class,
            'updates' => \Rhapsody\Core\Middleware\CheckUpdatesMiddleware::class,
            'docs'    => \Rhapsody\Core\Middleware\DocsAccessMiddleware::class,
        ],
        // Global middleware (runs on every matched request)
        'global' => [
            \Rhapsody\Core\Middleware\VerifyCsrfTokenMiddleware::class,
            \Rhapsody\Core\Middleware\DdosMiddleware::class,
        ],
    ],
    // DDoS protection settings
    'ddos_enabled'        => filter_var($_ENV['DDOS_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'ddos_max_requests'   => (int) ($_ENV['DDOS_MAX_REQUESTS'] ?? 60),
    'ddos_time_window'    => (int) ($_ENV['DDOS_TIME_WINDOW'] ?? 60),
    'ddos_block_duration' => (int) ($_ENV['DDOS_BLOCK_DURATION'] ?? 300),
    'ddos_whitelist'      => explode(',', $_ENV['DDOS_WHITELIST'] ?? '127.0.0.100,::100'),
    'ddos_blacklist'      => explode(',', $_ENV['DDOS_BLACKLIST'] ?? ''),
    'commands'            => [
        // Framework Core Engine System Commands
        \Rhapsody\Core\Commands\MakeControllerCommand::class,
        \Rhapsody\Core\Commands\MakeModelCommand::class,
        \Rhapsody\Core\Commands\MakeMiddlewareCommand::class,
        \Rhapsody\Core\Commands\MakeMigrationCommand::class,
        \Rhapsody\Core\Commands\MigrateCommand::class,
        \Rhapsody\Core\Commands\RouteCacheCommand::class,
        \Rhapsody\Core\Commands\RouteClearCommand::class,
        \Rhapsody\Core\Commands\CacheClearCommand::class,
        \Rhapsody\Core\Commands\CacheWarmCommand::class,
        \Rhapsody\Core\Commands\EnvSyncCommand::class,
        \Rhapsody\Core\Commands\UpdateCommand::class,
        \Rhapsody\Core\Commands\CheckVersionCommand::class,
        \Rhapsody\Core\Commands\MakeEventCommand::class,
        \Rhapsody\Core\Commands\MakeListenerCommand::class,
    ],
];
