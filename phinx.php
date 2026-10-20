<?php

declare (strict_types = 1);

require_once __DIR__ . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();

/**
 * Read an environment value, falling back to a default when unset or empty.
 */
$env = static function (string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? getenv($key);

    return is_string($value) && $value !== '' ? $value : $default;
};

$driver = strtolower($env('DB_DRIVER', 'mysql'));

$adapter = match ($driver) {
    'mysql', 'mariadb'  => 'mysql',
    'pgsql', 'postgres', 'postgresql' => 'pgsql',
    'sqlsrv', 'sqlserver', 'mssql'    => 'sqlsrv',
    'sqlite', 'sqlite3' => 'sqlite',
    default => throw new RuntimeException("Unsupported DB_DRIVER '{$driver}'."),
};

$name = $env('DB_NAME');

/**
 * @var array<string, mixed> $connection
 */
$connection = ['adapter' => $adapter];

if ($adapter === 'sqlite') {
    $isAbsolute = preg_match('#^(?:[A-Za-z]:[\\\\/]|[\\\\/])#', $name) === 1;

    $connection['name']   = $isAbsolute ? $name : __DIR__ . '/' . $name;
    $connection['suffix'] = ''; // Phinx appends ".sqlite3" by default; use the exact path instead.
} else {
    $connection['host'] = $env('DB_HOST', '127.0.0.1');
    $connection['name'] = $name;
    $connection['user'] = $env('DB_USER');
    $connection['pass'] = $env('DB_PASS');
    $connection['port'] = (int) $env('DB_PORT', match ($adapter) {
        'pgsql'  => '5432',
        'sqlsrv' => '1433',
        default  => '3306',
    });

    if ($adapter === 'mysql') {
        $connection['charset'] = 'utf8mb4';
    } elseif ($adapter === 'pgsql') {
        $connection['charset'] = 'utf8';
    }
}

return [
    'paths'         => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments'  => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'development',
        'development'             => $connection,
    ],
    'version_order' => 'creation',
];
