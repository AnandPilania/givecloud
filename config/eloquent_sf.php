<?php

return [
    // Set the layout used for default columns returned on select.
    'layout' => 'describe/compactLayouts/primary',

    'logging' => env('SOQL_LOG', 'single'),

    'batch' => [
        'select' => [
            'size' => 25,
        ],
        'insert' => [
            'size' => 200,
        ],
    ],

    // Override any Forrest settings here. The Forrest package config file is ignored, but all Forrest settings are supported here.
    'forrest' => [
        /*
         * Options include WebServer or UserPassword
         */
        'authentication' => 'WebServer',

        /*
         * Enter your credentials
         * Username and Password are only necessary for UserPassword flow.
         * Likewise, callbackURI is only necessary for WebServer flow.
         */
        'credentials' => config('database.connections.soql'),

        /*
         * These are optional authentication parameters that can be specified for the WebServer flow.
         * https://help.salesforce.com/apex/HTViewHelpDoc?id=remoteaccess_oauth_web_server_flow.htm&language=en_US
         */
        'parameters' => [
            'display' => '',
            'immediate' => false,
            'state' => '',
            'scope' => '',
            'prompt' => '',
        ],

        /*
         * Default settings for resource requests.
         * Format can be 'json', 'xml' or 'none'
         * Compression can be set to 'gzip' or 'deflate'
         */
        'defaults' => [
            'method' => 'get',
            'format' => 'json',
            'compression' => false,
            'compressionType' => 'gzip',
        ],

        /*
         * Where do you want to store access tokens fetched from Salesforce
         */
        'storage' => [
            'type' => 'Ds\\Facades\\SysConfig',
            // 'session' or 'cache' are the two options
            'path' => 'forrest_',
            // unique storage path to avoid collisions
            'expire_in' => 300, // number of minutes to expire cache/session
            'store_forever' => true, // never expire cache/session
        ],

        /*
         * If you'd like to specify an API version manually it can be done here.
         * Format looks like '32.0'
         */
        'version' => '',
    ],
];
