<?php

return [
    'client_id'     => env('DOKU_CLIENT_ID'),
    'secret_key'    => env('DOKU_SECRET_KEY'),
    'merchant_id'   => env('DOKU_MERCHANT_ID'),
    'terminal_id'   => env('DOKU_TERMINAL_ID', 'H2H'),
    'is_production' => env('DOKU_IS_PRODUCTION', false),
];
