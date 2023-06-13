<?php

return [
    'config' => [
        'conversation_cache_time' => 40,
        'user_cache_time' => 30,
    ],

    'nexmo' => [
        'app_key' => env('NEXMO_APP_KEY'),
        'app_secret' => env('NEXMO_APP_SECRET'),
        'sender' => env('NEXMO_SENDER'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'fromNumber' => env('TWILIO_FROM_NUMBER'),
        'token' => env('TWILIO_TOKEN'),
        'language' => 'en',
        'voice' => \BotMan\Drivers\Twilio\TwilioSettings::VOICE_MAN,
        'input' => \BotMan\Drivers\Twilio\TwilioSettings::INPUT_DTMF,
    ],

    'web' => [
        'matchingData' => [
            'driver' => 'web',
        ],
    ],
];
