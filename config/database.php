<?php

use Illuminate\Support\Str;

$valueOrDefault = static function ($value, $default = null) {
    return ($value !== null && $value !== '') ? $value : $default;
};

$prefer = static function (bool $preferPrimary, $primary, $secondary, $default = null) use ($valueOrDefault) {
    if ($preferPrimary) {
        return $valueOrDefault($primary, $valueOrDefault($secondary, $default));
    }
    return $valueOrDefault($secondary, $valueOrDefault($primary, $default));
};

$normalizeConnection = static function ($value): ?string {
    $normalized = strtolower(trim((string) $value));

    return in_array($normalized, ['sqlite', 'mysql', 'pgsql', 'sqlsrv'], true)
        ? $normalized
        : null;
};

$databaseUrl = trim((string) env('DATABASE_URL', ''));
$databaseUrlConnection = null;
if ($databaseUrl !== '') {
    $databaseUrlScheme = strtolower((string) parse_url($databaseUrl, PHP_URL_SCHEME));
    $databaseUrlConnection = match ($databaseUrlScheme) {
        'mysql' => 'mysql',
        'pgsql', 'postgres', 'postgresql' => 'pgsql',
        'sqlsrv', 'mssql' => 'sqlsrv',
        'sqlite' => 'sqlite',
        default => null,
    };
}

$mysqlEnvDetected = (bool) (
    env('MYSQLHOST') ||
    env('MYSQLDATABASE') ||
    env('MYSQLUSER') ||
    env('MYSQL_URL') ||
    env('MYSQLPORT') ||
    env('MYSQLPASSWORD')
);

$pgsqlEnvDetected = (bool) (
    env('PGHOST') ||
    env('PGDATABASE') ||
    env('PGUSER') ||
    env('POSTGRES_URL') ||
    env('PGPORT') ||
    env('PGPASSWORD')
);

$defaultConnectionFromEnv = $normalizeConnection(env('DB_CONNECTION'));
if ($databaseUrlConnection !== null) {
    $defaultConnection = $databaseUrlConnection;
} elseif ($mysqlEnvDetected && !$pgsqlEnvDetected) {
    $defaultConnection = 'mysql';
} elseif ($pgsqlEnvDetected && !$mysqlEnvDetected) {
    $defaultConnection = 'pgsql';
} elseif ($defaultConnectionFromEnv !== null) {
    $defaultConnection = $defaultConnectionFromEnv;
} else {
    $defaultConnection = 'mysql';
}

$mysqlUrl = $databaseUrlConnection === 'mysql'
    ? $databaseUrl
    : $valueOrDefault(env('MYSQL_URL'), null);
$pgsqlUrl = $databaseUrlConnection === 'pgsql'
    ? $databaseUrl
    : $valueOrDefault(env('POSTGRES_URL'), null);
$sqlsrvUrl = $databaseUrlConnection === 'sqlsrv' ? $databaseUrl : null;
$sqliteUrl = $databaseUrlConnection === 'sqlite' ? $databaseUrl : null;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => $defaultConnection,

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => $sqliteUrl,
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            // Railway compatibility: prioritize provider vars and MYSQL URL.
            'url' => $prefer($mysqlEnvDetected, $mysqlUrl, $databaseUrlConnection === 'mysql' ? $databaseUrl : null),
            'host' => $prefer($mysqlEnvDetected, env('MYSQLHOST'), env('DB_HOST'), '127.0.0.1'),
            'port' => $prefer($mysqlEnvDetected, env('MYSQLPORT'), env('DB_PORT'), '3306'),
            'database' => $prefer($mysqlEnvDetected, env('MYSQLDATABASE'), env('DB_DATABASE'), 'forge'),
            'username' => $prefer($mysqlEnvDetected, env('MYSQLUSER'), env('DB_USERNAME'), 'forge'),
            'password' => $prefer($mysqlEnvDetected, env('MYSQLPASSWORD'), env('DB_PASSWORD'), ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => $prefer($pgsqlEnvDetected, $pgsqlUrl, $databaseUrlConnection === 'pgsql' ? $databaseUrl : null),
            'host' => $prefer($pgsqlEnvDetected, env('PGHOST'), env('DB_HOST'), '127.0.0.1'),
            'port' => $prefer($pgsqlEnvDetected, env('PGPORT'), env('DB_PORT'), '5432'),
            'database' => $prefer($pgsqlEnvDetected, env('PGDATABASE'), env('DB_DATABASE'), 'forge'),
            'username' => $prefer($pgsqlEnvDetected, env('PGUSER'), env('DB_USERNAME'), 'forge'),
            'password' => $prefer($pgsqlEnvDetected, env('PGPASSWORD'), env('DB_PASSWORD'), ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => $sqlsrvUrl,
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
