<?php

return [
    'analytics' => [
        'visit_timeout' => 30, // minutes
    ],

    'missioncontrol_domain' => env('GIVECLOUD_MISSIONCONTROL_DOMAIN', 'missioncontrol.givecloud.com'),
    'sites_domain' => env('GIVECLOUD_SITES_DOMAIN', 'givecloud.co'),

    'super_user_id' => 1,
];
