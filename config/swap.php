<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Options.
    |--------------------------------------------------------------------------
    |
    | The options to pass to Swap amongst:
    |
    | * cache_ttl: The cache ttl in seconds.
    */
    'options' => [
        'cache_ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    |
    | This option specifies the services to use with their name as key and
    | their config as value.
    |
    */

    'services' => [
        'fixer' => [
            'access_key' => env('FIXERIO_API_KEY'),
            'enterprise' => true,
        ],

        'currency_data_feed' => [
            'api_key' => env('CURRENCYDATAFEED_API_KEY'),
        ],

        'forge' => [
            'api_key' => env('1FORGE_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | This option specifies the Laravel cache store to use.
    |
    | 'cache' => 'file'
    */

    'cache' => 'app',

    /*
    |--------------------------------------------------------------------------
    | Http Client.
    |--------------------------------------------------------------------------
    |
    | The HTTP client service name to use.
    */

    'http_client' => 'swap.http_client',

    /*
    |--------------------------------------------------------------------------
    | Request Factory.
    |--------------------------------------------------------------------------
    |
    | The Request Factory service name to use.
    */

    'request_factory' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache Item Pool.
    |--------------------------------------------------------------------------
    |
    | The Cache Item Pool service name to use.
    */

    'cache_item_pool' => null,
];
