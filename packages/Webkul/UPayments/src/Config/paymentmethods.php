<?php

use Webkul\UPayments\Payment\UPaymentsApplePay;
use Webkul\UPayments\Payment\UPaymentsCreditCard;
use Webkul\UPayments\Payment\UPaymentsGooglePay;
use Webkul\UPayments\Payment\UPaymentsKnet;
use Webkul\UPayments\Payment\UPaymentsSamsungPay;

return [
    'upayments_knet' => [
        'code'        => 'upayments_knet',
        'title'       => 'KNET',
        'description' => 'Pay with KNET',
        'class'       => UPaymentsKnet::class,
        'sandbox'     => true,
        'active'      => true,
        'sort'        => 3,
    ],

    'upayments_cc' => [
        'code'        => 'upayments_cc',
        'title'       => 'Credit / Debit Card',
        'description' => 'Pay with Credit or Debit Card',
        'class'       => UPaymentsCreditCard::class,
        'sandbox'     => true,
        'active'      => true,
        'sort'        => 4,
    ],

    'upayments_apple_pay' => [
        'code'        => 'upayments_apple_pay',
        'title'       => 'Apple Pay',
        'description' => 'Pay with Apple Pay',
        'class'       => UPaymentsApplePay::class,
        'sandbox'     => true,
        'active'      => true,
        'sort'        => 5,
    ],

    'upayments_google_pay' => [
        'code'        => 'upayments_google_pay',
        'title'       => 'Google Pay',
        'description' => 'Pay with Google Pay',
        'class'       => UPaymentsGooglePay::class,
        'sandbox'     => true,
        'active'      => true,
        'sort'        => 6,
    ],

    'upayments_samsung_pay' => [
        'code'        => 'upayments_samsung_pay',
        'title'       => 'Samsung Pay',
        'description' => 'Pay with Samsung Pay',
        'class'       => UPaymentsSamsungPay::class,
        'sandbox'     => true,
        'active'      => true,
        'sort'        => 7,
    ],
];
