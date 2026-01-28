<?php

return [
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'host' => env('PUSHER_HOST'),
        'port' => env('PUSHER_PORT'),
        'scheme' => env('PUSHER_SCHEME'),
        'useTLS' => false,
    ],
];