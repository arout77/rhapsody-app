<?php

declare (strict_types = 1);

use Rhapsody\Core\Support\Env;

require_once __DIR__ . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();

$adapter = match (strtolower(Env::string('DB_DRIVER', 'mysql'))) {
    'mysql', 'mariadb' => 'mysql',
    'pgsql', 'postgres', 'postgresql' => 'pgsql',
    'sqlsrv', 'sqlserver', 'mssql'    => 'sqlsrv',
    'sqlite' => 'sqlite',
    default  => throw new RuntimeException(
        'Unsupported DB_DRIVER "' . Env::string('DB_DRIVER') . '". Use mysql, pgsql, sqlsrv or sqlite.'
    ),
};

$required = $adapter === 'sqlite' ? ['DB_NAME'] : ['DB_HOST', 'DB_NAME', 'DB_USER'];
$missing  = array_values(array_filter($required, static fn(string $key): bool => Env::string($key) === ''));

if ($missing !== []) {
    throw new RuntimeException(
        'Cannot run migrations: missing ' . implode(', ', $missing) . ' in .env.'
    );
}

$defaultPort = match ($adapter) {
    'pgsql'  => 5432,
    'sqlsrv' => 1433,
    default  => 3306,
};

$connection = [
    'adapter' => $adapter,
    'name'    => Env::string('DB_NAME'),
];

if ($adapter !== 'sqlite') {
    $connection += [
        'host' => Env::string('DB_HOST'),
        'user' => Env::string('DB_USER'),
        'pass' => Env::string('DB_PASS'),
        'port' => Env::int('DB_PORT', $defaultPort),
    ];
}

if ($adapter === 'mysql') {
    $connection['charset'] = 'utf8mb4';
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
