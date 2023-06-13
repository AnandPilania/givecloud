<?php

$defaultDatabaseConfig = [
    'driver' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'sys-backend'),
    'username' => env('DB_USERNAME', ''),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => false,
    'engine' => null,
];

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

    'default' => env('DB_CONNECTION', 'sys-backend'),

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
        'sys-backend' => $defaultDatabaseConfig,

        'givecloud-db-0' => $defaultDatabaseConfig,
        'givecloud-db-1' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_1', 'localhost')]),
        'givecloud-db-2' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_2', 'localhost')]),
        'givecloud-db-3' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_3', 'localhost')]),
        'givecloud-db-4' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_4', 'localhost')]),
        'givecloud-db-5' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_5', 'localhost')]),
        'givecloud-db-6' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_6', 'localhost')]),
        'givecloud-db-7' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_7', 'localhost')]),
        'givecloud-db-8' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_8', 'localhost')]),
        'givecloud-db-9' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_9', 'localhost')]),
        'givecloud-db-10' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_10', 'localhost')]),
        'givecloud-db-11' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_11', 'localhost')]),
        'givecloud-db-12' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_12', 'localhost')]),
        'givecloud-db-13' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_13', 'localhost')]),
        'givecloud-db-14' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_14', 'localhost')]),
        'givecloud-db-15' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_15', 'localhost')]),
        'givecloud-db-16' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_16', 'localhost')]),
        'givecloud-db-17' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_17', 'localhost')]),
        'givecloud-db-18' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_18', 'localhost')]),
        'givecloud-db-19' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_19', 'localhost')]),
        'givecloud-db-20' => array_merge($defaultDatabaseConfig, ['host' => env('GIVECLOUD_DB_20', 'localhost')]),

        // use for quoting and other query
        // fakery in the DPO query builder
        'dpo' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ],

        'testing' => array_merge($defaultDatabaseConfig, ['database' => 'testing']),

        // Salesforce
        'soql' => [
            'driver' => 'soql',
            'database' => null,
            'consumerKey' => null,
            'consumerSecret' => null,
            'loginURL' => env('SALESFORCE_LOGIN_URL', 'https://login.salesforce.com'),
            'callbackURI' => null,
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
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),

        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

        'sites-queue' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 1,
        ],

        'sites-cache' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DBAL Mappings
    |--------------------------------------------------------------------------
    |
    | If your migrations include ALTER commands then the library
    | `doctrine/dbal` is used to make those changes.  That library supports
    | Laravel specific data type(s).  This section maps those datatypes.
    |
    */

    'dbal' => [
        'types' => [
            'char' => \Doctrine\DBAL\Types\StringType::class,
            'double' => \Doctrine\DBAL\Types\FloatType::class,
            'integer11' => \Ds\Illuminate\Database\DBAL\Integer11Type::class,
            'timestamp' => \Illuminate\Database\DBAL\TimestampType::class,
        ],
    ],
];
