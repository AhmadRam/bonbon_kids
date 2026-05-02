<?php

return [
    // ─── Shared API Settings (stored under upayments key) ───────────────────
    [
        'key'    => 'sales.payment_methods.upayments',
        'name'   => 'upayments::app.upayments-settings',
        'info'   => 'upayments::app.upayments-settings-info',
        'sort'   => 3,
        'fields' => [
            [
                'name'          => 'sandbox',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.sandbox',
                'type'          => 'boolean',
                'default'       => true,
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'api_key',
                'title'         => 'upayments::app.api-key',
                'type'          => 'text',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'api_test_key',
                'title'         => 'upayments::app.api-test-key',
                'type'          => 'text',
                'default'       => 'e66a94d579cf75fba327ff716ad68c53aae11528',
                'channel_based' => true,
                'locale_based'  => false,
            ],
        ],
    ],

    // ─── KNET ────────────────────────────────────────────────────────────────
    [
        'key'    => 'sales.payment_methods.upayments_knet',
        'name'   => 'upayments::app.knet.title',
        'sort'   => 4,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.title',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'active',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.status',
                'type'          => 'boolean',
                'default'       => true,
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'    => 'sort',
                'title'   => 'admin::app.configuration.index.sales.payment-methods.sort-order',
                'type'    => 'select',
                'options' => [
                    ['title' => '1', 'value' => 1],
                    ['title' => '2', 'value' => 2],
                    ['title' => '3', 'value' => 3],
                    ['title' => '4', 'value' => 4],
                    ['title' => '5', 'value' => 5],
                    ['title' => '6', 'value' => 6],
                    ['title' => '7', 'value' => 7],
                ],
            ],
        ],
    ],

    // ─── Credit / Debit Card ─────────────────────────────────────────────────
    [
        'key'    => 'sales.payment_methods.upayments_cc',
        'name'   => 'upayments::app.cc.title',
        'sort'   => 5,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.title',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'active',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.status',
                'type'          => 'boolean',
                'default'       => true,
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'    => 'sort',
                'title'   => 'admin::app.configuration.index.sales.payment-methods.sort-order',
                'type'    => 'select',
                'options' => [
                    ['title' => '1', 'value' => 1],
                    ['title' => '2', 'value' => 2],
                    ['title' => '3', 'value' => 3],
                    ['title' => '4', 'value' => 4],
                    ['title' => '5', 'value' => 5],
                    ['title' => '6', 'value' => 6],
                    ['title' => '7', 'value' => 7],
                ],
            ],
        ],
    ],

    // ─── Apple Pay ───────────────────────────────────────────────────────────
    [
        'key'    => 'sales.payment_methods.upayments_apple_pay',
        'name'   => 'upayments::app.apple-pay.title',
        'sort'   => 6,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.title',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'active',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.status',
                'type'          => 'boolean',
                'default'       => true,
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'    => 'sort',
                'title'   => 'admin::app.configuration.index.sales.payment-methods.sort-order',
                'type'    => 'select',
                'options' => [
                    ['title' => '1', 'value' => 1],
                    ['title' => '2', 'value' => 2],
                    ['title' => '3', 'value' => 3],
                    ['title' => '4', 'value' => 4],
                    ['title' => '5', 'value' => 5],
                    ['title' => '6', 'value' => 6],
                    ['title' => '7', 'value' => 7],
                ],
            ],
        ],
    ],

    // ─── Google Pay ──────────────────────────────────────────────────────────
    [
        'key'    => 'sales.payment_methods.upayments_google_pay',
        'name'   => 'upayments::app.google-pay.title',
        'sort'   => 7,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.title',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'active',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.status',
                'type'          => 'boolean',
                'default'       => true,
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'    => 'sort',
                'title'   => 'admin::app.configuration.index.sales.payment-methods.sort-order',
                'type'    => 'select',
                'options' => [
                    ['title' => '1', 'value' => 1],
                    ['title' => '2', 'value' => 2],
                    ['title' => '3', 'value' => 3],
                    ['title' => '4', 'value' => 4],
                    ['title' => '5', 'value' => 5],
                    ['title' => '6', 'value' => 6],
                    ['title' => '7', 'value' => 7],
                ],
            ],
        ],
    ],

    // ─── Samsung Pay ─────────────────────────────────────────────────────────
    [
        'key'    => 'sales.payment_methods.upayments_samsung_pay',
        'name'   => 'upayments::app.samsung-pay.title',
        'sort'   => 8,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.title',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'active',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.status',
                'type'          => 'boolean',
                'default'       => true,
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'    => 'sort',
                'title'   => 'admin::app.configuration.index.sales.payment-methods.sort-order',
                'type'    => 'select',
                'options' => [
                    ['title' => '1', 'value' => 1],
                    ['title' => '2', 'value' => 2],
                    ['title' => '3', 'value' => 3],
                    ['title' => '4', 'value' => 4],
                    ['title' => '5', 'value' => 5],
                    ['title' => '6', 'value' => 6],
                    ['title' => '7', 'value' => 7],
                ],
            ],
        ],
    ],
];
