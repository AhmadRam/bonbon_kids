<?php

return [
    'base_url' => env('DAFTRA_BASE_URL', 'https://elkasaby2024.daftra.com'),
    'client_id' => env('DAFTRA_CLIENT_ID', '1'),
    'client_secret' => env('DAFTRA_CLIENT_SECRET'),
    'username' => env('DAFTRA_USERNAME'),
    'password' => env('DAFTRA_PASSWORD'),
    'api_key' => env('DAFTRA_API_KEY'),
    'store_id' => (int) env('DAFTRA_STORE_ID', 2),
    'invoice_layout_id' => (int) env('DAFTRA_INVOICE_LAYOUT_ID', 13),
    'payment_method' => env('DAFTRA_PAYMENT_METHOD', 'cash_on_delivery'),
    'upayments_treasury_id' => (int) env('DAFTRA_UPAYMENTS_TREASURY_ID', 3),
    'default_treasury_id' => (int) env('DAFTRA_DEFAULT_TREASURY_ID', 1),
    'checkout_stock_check' => (bool) env('DAFTRA_CHECKOUT_STOCK_CHECK', true),
    'checkout_stock_timeout' => (float) env('DAFTRA_CHECKOUT_STOCK_TIMEOUT', 2.5),
    'checkout_stock_connect_timeout' => (float) env('DAFTRA_CHECKOUT_STOCK_CONNECT_TIMEOUT', 1.5),
    'checkout_stock_cache_ttl' => (int) env('DAFTRA_CHECKOUT_STOCK_CACHE_TTL', 20),
];
