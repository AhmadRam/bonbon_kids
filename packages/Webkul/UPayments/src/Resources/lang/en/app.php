<?php

return [
    'upayments-settings'      => 'UPayments — API Settings',
    'upayments-settings-info' => 'Configure your UPayments API credentials (shared across all UPayments methods).',
    'api-key'                 => 'API Key (Production)',
    'api-test-key'            => 'API Key (Sandbox / Test)',

    'knet'        => ['title' => 'KNET',                   'description' => 'Pay with KNET'],
    'cc'          => ['title' => 'Credit / Debit Card',    'description' => 'Pay with Credit or Debit Card'],
    'apple-pay'   => ['title' => 'Apple Pay',              'description' => 'Pay with Apple Pay'],
    'google-pay'  => ['title' => 'Google Pay',             'description' => 'Pay with Google Pay'],
    'samsung-pay' => ['title' => 'Samsung Pay',            'description' => 'Pay with Samsung Pay'],

    'response' => [
        'cart-not-found'      => 'Cart not found or invalid.',
        'cart-processed'      => 'This cart has already been processed.',
        'invalid-method'      => 'Invalid payment method selected.',
        'invalid-request'     => 'Invalid payment callback request.',
        'payment-cancelled'   => 'Payment was cancelled.',
        'payment-failed'      => 'Payment failed. Please try again.',
        'payment-success'     => 'Payment completed successfully.',
        'provide-credentials' => 'UPayments API key is not configured. Please contact support.',
        'redirect-failed'     => 'Could not initiate payment. Please try again.',
        'verification-failed' => 'Payment verification failed.',
    ],
];
