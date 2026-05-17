<?php

return [
    'api' => [
        'universalis' => [
            'base_url'  => env('UNIVERSALIS_URL', 'https://universalis.app/api/v2'),
            'timeout'   => 10,
            'cache_ttl' => 600,
        ],
        'xivapi' => [
            'base_url'    => env('XIVAPI_URL', 'https://xivapi.com'),
            'v2_base_url' => env('XIVAPI_V2_URL', 'https://v2.xivapi.com'),
            'timeout'     => 15,
            'cache_ttl'   => 86400,
        ],
    ],
    'analysis' => [
        'default_server'        => env('DEFAULT_SERVER', 'Cactuar'),
        'max_results'           => 1000,
        'background_processing' => env('BACKGROUND_PROCESSING', false),
    ],
];
