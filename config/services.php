<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'missioncontrol' => [
        'api_token' => env('MISSIONCONTROL_API_TOKEN'),
        'plans' => [
            'lite' => env('MISSIONCONTROL_LITE_PLAN_NAME'),
            'impact' => env('MISSIONCONTROL_IMPACT_PLAN_NAME'),
        ],
    ],

    'bugsnag' => [
        'php_api_key' => env('BUGSNAG_PHP_API_KEY'),
        'js_api_key' => env('BUGSNAG_JS_API_KEY'),
    ],

    'canny' => [
        'board_token' => env('CANNY_BOARD_TOKEN'),
        'private_key' => env('CANNY_PRIVATE_KEY'),
        'company_id' => env('CANNY_COMPANY_ID'),
        'redirect_url' => env('CANNY_REDIRECT_URL'),
    ],

    'chargebee' => [
        'site' => env('CHARGEBEE_SITE'),
        'key' => env('CHARGEBEE_KEY'),
        'gateway_account' => env('CHARGEBEE_GATEWAY_ACCOUNT'),
        'plans' => [
            'cad' => [
                'monthly' => [
                    'lite' => env('CHARGEBEE_CAD_LITE_MONTHLY'),
                    'impact' => env('CHARGEBEE_CAD_IMPACT_MONTHLY'),
                ],
                'annually' => [
                    'lite' => env('CHARGEBEE_CAD_LITE_ANNUALLY'),
                    'impact' => env('CHARGEBEE_CAD_IMPACT_ANNUALY'),
                ],
            ],
            '*' => [
                'monthly' => [
                    'lite' => env('CHARGEBEE_OTHERS_LITE_MONTHLY'),
                    'impact' => env('CHARGEBEE_OTHERS_IMPACT_MONTHLY'),
                ],
                'annually' => [
                    'lite' => env('CHARGEBEE_OTHERS_LITE_ANNUALLY'),
                    'impact' => env('CHARGEBEE_OTHERS_IMPACT_ANNUALY'),
                ],
            ],
        ],
    ],

    'double-the-donation' => [
        'partner_id' => env('DOUBLE_THE_DONATION_PARTNER_ID'),
    ],

    // Used by social Login
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('SOCIALITE_CALLBACK_URL'),
        'supporter_redirect' => env('SOCIALITE_SUPPORTER_CALLBACK_URL'),
    ],

    'flatfile' => [
        'team_id' => env('FLATFILE_TEAM_ID'),
        'access_key' => env('FLATFILE_ACCESS_KEY_ID'),
        'private_key' => env('FLATFILE_ACCESS_SECRET'),
        'embeds' => [
            'contributions' => [
                'id' => env('FLATFILE_CONTRIBUTIONS_EMBED_ID'),
                'key' => env('FLATFILE_CONTRIBUTIONS_PRIVATE_KEY'),
            ],
            'supporters' => [
                'id' => env('FLATFILE_SUPPORTERS_EMBED_ID'),
                'key' => env('FLATFILE_SUPPORTERS_EMBED_PRIVATE_KEY'),
            ],
            'sponsorships' => [
                'id' => env('FLATFILE_SPONSORSHIPS_EMBED_ID'),
                'key' => env('FLATFILE_SPONSORSHIPS_PRIVATE_KEY'),
            ],
        ],
    ],

    'geoip' => [
        'database' => env('GEOIP_DATABASE', '/var/lib/GeoIP/GeoIP2-City.mmdb'),
    ],

    'ip2proxy' => [
        'database' => env('IP2PROXY_DATABASE', '/var/lib/GeoIP/IP2PROXY-LITE-PX3.BIN'),
    ],

    // Used by social Login
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('SOCIALITE_CALLBACK_URL'),
        'supporter_redirect' => env('SOCIALITE_SUPPORTER_CALLBACK_URL'),
    ],

    'google-maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'google-storage' => [
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', env('GOOGLE_CLOUD_PROJECT')),
        'key_file' => env('GOOGLE_CLOUD_KEY_FILE', env('GOOGLE_APPLICATION_CREDENTIALS')),
        'cdn_bucket' => 'cdn.givecloud.co',
    ],

    'hcaptcha' => [
        'site_key' => env('HCAPTCHA_SITE_KEY'),
        'secret_key' => env('HCAPTCHA_SECRET_KEY'),
    ],

    'hotglue' => [
        'env_id' => env('HOTGLUE_ENV_ID'),
        'api_key' => env('HOTGLUE_API_KEY'),
        'private_key' => env('HOTGLUE_PRIVATE_KEY'),
        'hubspot' => [
            'flow_id' => env('HOTGLUE_HUBSPOT_FLOW_ID'),
            'target_id' => env('HOTGLUE_HUBSPOT_TARGET_ID'),
        ],
        'salesforce' => [
            'flow_id' => env('HOTGLUE_SALESFORCE_FLOW_ID'),
            'target_id' => env('HOTGLUE_SALESFORCE_TARGET_ID'),
        ],
        'mailchimp' => [
            'flow_id' => env('HOTGLUE_MAILCHIMP_FLOW_ID'),
            'target_id' => env('HOTGLUE_MAILCHIMP_TARGET_ID'),
        ],
    ],

    'infusionsoft' => [
        'client_id' => env('INFUSIONSOFT_CLIENT_ID'),
        'client_secret' => env('INFUSIONSOFT_CLIENT_SECRET'),
        'redirect' => env('INFUSIONSOFT_REDIRECT_URL', 'https://app.givecloud.co/connect/infusionsoft'),
        'debug' => env('INFUSIONSOFT_DEBUG'),
    ],

    'intercom' => [
        'access_token' => env('INTERCOM_ACCESS_TOKEN'),
        'identity_verification' => env('INTERCOM_IDENTITY_VERIFICATION'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'datadog' => [
        'api_key' => env('DATADOG_API_KEY'),
        'app_key' => env('DATADOG_APP_KEY'),
    ],

    'nexmo' => [
        'app_key' => env('NEXMO_APP_KEY'),
        'app_secret' => env('NEXMO_APP_SECRET'),
    ],

    'stripe' => [
        'model' => Ds\Models\User::class,
        'key' => env('STRIPE_PROD_PUBLISHABLE_KEY'),
        'secret' => env('STRIPE_PROD_SECRET_KEY'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
    ],

    'mux' => [
        'token_id' => env('MUX_ACCESS_TOKEN_ID'),
        'secret_key' => env('MUX_SECRET_KEY'),
        'stream_url' => 'rtmps://global-live.mux.com:443/app',
    ],

    // Used by social Login
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('SOCIALITE_CALLBACK_URL'),
        'supporter_redirect' => env('SOCIALITE_SUPPORTER_CALLBACK_URL'),
    ],

    'zapier' => [
        'client_secret' => env('ZAPIER_CLIENT_SECRET'),
        'redirect' => env('ZAPIER_REDIRECT_URL'),
    ],
];
