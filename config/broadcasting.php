<?php
// config/broadcasting.php

return [
    'default' => env('BROADCAST_DRIVER', 'pusher'),

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY', 'escape-game-key'),
            'secret' => env('PUSHER_APP_SECRET', 'escape-game-secret'),
            'app_id' => env('PUSHER_APP_ID', 'escape-game-app'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'host' => '127.0.0.1',
                'port' => 6001,
                'scheme' => 'http',
                'encrypted' => false,
                'useTLS' => false,
            ],
        ],
    ],
];