<?php

return [
    'mode'    => env('PAYPAL_MODE', 'sandbox'), // 'live' для продакшна
    'sandbox' => [
        'client_id'     => env('PAYPAL_SANDBOX_CLIENT_ID'),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
    ],
    'live' => [
        'client_id'     => env('PAYPAL_LIVE_CLIENT_ID'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
    ],
    'payment_action' => 'Sale',
    'notify_url'     => '', // можно добавить вебхук при желании
    'validate_ssl'   => true,
];
