<?php

use Illuminate\Support\Str;

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

    'default' => env('DB_CONNECTION', 'oracle'),

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
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
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
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],
        'telescope_mysql' => [
            'driver' => 'mysql',
            'host' => env('TELESCOPE_DB_HOST', '127.0.0.1'),
            'port' => env('TELESCOPE_DB_PORT', '3306'),
            'database' => env('TELESCOPE_DB_DATABASE', 'laravel_test_tele'),
            'username' => env('TELESCOPE_DB_USERNAME', 'root'),
            'password' => env('TELESCOPE_DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'oracle_his' => [
            'driver' => 'oracle',
            'host' => env('DB_HIS_HOST', '192.168.101.251'),
            'port' => env('DB_HIS_PORT', '1521'),
            'database' => env('DB_HIS_DATABASE', 'oracletest'),
            'username' => env('DB_HIS_USERNAME', 'HIS_RS'),
            'password' => env('DB_HIS_PASSWORD', 'HIS_RS'),
            'service_name' => env('DB_HIS_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
        ],
        'oracle_acs' => [
            'driver' => 'oracle',
            'host' => env('DB_ACS_HOST', '192.168.101.251'),
            'port' => env('DB_ACS_PORT', '1521'),
            'database' => env('DB_ACS_DATABASE', 'oracletest'),
            'username' => env('DB_ACS_USERNAME', 'ACS_RS'),
            'password' => env('DB_ACS_PASSWORD', 'ACS_RS'),
            'service_name' => env('DB_ACS_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
        ],
        'oracle_sda' => [
            'driver' => 'oracle',
            'host' => env('DB_SDA_HOST', '192.168.101.251'),
            'port' => env('DB_SDA_PORT', '1521'),
            'database' => env('DB_SDA_DATABASE', 'oracletest'),
            'username' => env('DB_SDA_USERNAME', 'SDA_RS'),
            'password' => env('DB_SDA_PASSWORD', 'SDA_RS'),
            'service_name' => env('DB_SDA_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
        ],
        'oracle_emr' => [
            'driver' => 'oracle',
            'host' => env('DB_EMR_HOST', '192.168.101.251'),
            'port' => env('DB_EMR_PORT', '1521'),
            'database' => env('DB_EMR_DATABASE', 'oracletest'),
            'username' => env('DB_EMR_USERNAME', 'EMR_RS'),
            'password' => env('DB_EMR_PASSWORD', 'EMR_RS'),
            'service_name' => env('DB_EMR_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
        ],
        'elasticsearch' => [
            'driver' => 'elasticsearch',
            'hosts' => [
                [
                    'host' => env('ELASTICSEARCH_HOST', 'localhost'),
                    'port' => env('ELASTICSEARCH_PORT', 9200),
                    'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
                    'user' => env('ELASTICSEARCH_USER'),
                    'pass' => env('ELASTICSEARCH_PASS'),
                ],
            ],
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
        'expire' => 86400, // Thời gian hết hạn mặc định cho cache (đơn vị: giây)
        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            // 'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
            'prefix' => "",
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
