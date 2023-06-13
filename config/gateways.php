<?php

return [
    'gocardless' => [
        'prod' => [
            'app_id' => env('GOCARDLESS_PROD_APP_ID'),
            'client_id' => env('GOCARDLESS_PROD_CLIENT_ID'),
            'client_secret' => env('GOCARDLESS_PROD_CLIENT_SECRET'),
            'environment' => 'live',
            'return_url' => 'https://app.givecloud.co/connect/gocardless',
        ],
        'test' => [
            'app_id' => env('GOCARDLESS_TEST_APP_ID'),
            'client_id' => env('GOCARDLESS_TEST_CLIENT_ID'),
            'client_secret' => env('GOCARDLESS_TEST_CLIENT_SECRET'),
            'environment' => 'sandbox',
            'return_url' => 'https://app.givecloud.test/connect/gocardless',
        ],
    ],

    'paypal' => [
        'prod' => [
            'classic' => [
                'partner_id' => env('PAYPAL_PROD_CLASSIC_PARTNER_ID'),
                'api_username' => env('PAYPAL_PROD_CLASSIC_API_USERNAME'),
                'api_password' => env('PAYPAL_PROD_CLASSIC_API_PASSWORD'),
                'signature' => env('PAYPAL_PROD_CLASSIC_SIGNATURE'),
                'app_id' => env('PAYPAL_PROD_CLASSIC_APP_ID'),
            ],
            'rest' => [
                'client_id' => env('PAYPAL_PROD_REST_CLIENT_ID'),
                'secret' => env('PAYPAL_PROD_REST_SECRET'),
            ],
        ],
        'test' => [
            'classic' => [
                'partner_id' => env('PAYPAL_TEST_CLASSIC_PARTNER_ID'),
                'api_username' => env('PAYPAL_TEST_CLASSIC_API_USERNAME'),
                'api_password' => env('PAYPAL_TEST_CLASSIC_API_PASSWORD'),
                'signature' => env('PAYPAL_TEST_CLASSIC_SIGNATURE'),
                'app_id' => env('PAYPAL_TEST_CLASSIC_APP_ID'),
            ],
            'rest' => [
                'client_id' => env('PAYPAL_TEST_REST_CLIENT_ID'),
                'secret' => env('PAYPAL_TEST_REST_SECRET'),
            ],
        ],
        'bn_code' => env('PAYPAL_BN_CODE', 'PayPal_SDK'),
        'caching' => [
            'enabled' => true,
            'filename' => storage_path('framework/cache/paypal_cache'),
        ],
        'logging' => [
            'log_enabled' => true,
            'filename' => storage_path('logs/paypal_log'),
            'log_level' => env('PAYPAL_LOG_LEVEL', 'WARN'),
        ],
    ],

    'stripe' => [
        'api_version' => env('STRIPE_API_VERSION', '2017-06-05'),
        'prod' => [
            'publishable_key' => env('STRIPE_PROD_PUBLISHABLE_KEY'),
            'secret_key' => env('STRIPE_PROD_SECRET_KEY'),
            'client_id' => env('STRIPE_PROD_CLIENT_ID'),
        ],
        'test' => [
            'publishable_key' => env('STRIPE_TEST_PUBLISHABLE_KEY'),
            'secret_key' => env('STRIPE_TEST_SECRET_KEY'),
            'client_id' => env('STRIPE_TEST_CLIENT_ID'),
        ],
    ],
];
