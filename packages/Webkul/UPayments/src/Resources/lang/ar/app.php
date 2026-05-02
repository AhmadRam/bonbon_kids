<?php

return [
    'upayments-settings'      => 'UPayments — إعدادات API',
    'upayments-settings-info' => 'أدخل بيانات API الخاصة بـ UPayments (مشتركة بين جميع طرق الدفع).',
    'api-key'                 => 'مفتاح API (الإنتاج)',
    'api-test-key'            => 'مفتاح API (تجريبي / Sandbox)',

    'knet'        => ['title' => 'KNET',                   'description' => 'الدفع عبر KNET'],
    'cc'          => ['title' => 'بطاقة ائتمانية / مدى',  'description' => 'الدفع ببطاقة ائتمانية أو مدى'],
    'apple-pay'   => ['title' => 'Apple Pay',              'description' => 'الدفع عبر Apple Pay'],
    'google-pay'  => ['title' => 'Google Pay',             'description' => 'الدفع عبر Google Pay'],
    'samsung-pay' => ['title' => 'Samsung Pay',            'description' => 'الدفع عبر Samsung Pay'],

    'response' => [
        'cart-not-found'      => 'السلة غير موجودة أو غير صالحة.',
        'cart-processed'      => 'هذه السلة تمت معالجتها مسبقاً.',
        'invalid-method'      => 'طريقة الدفع المختارة غير صالحة.',
        'invalid-request'     => 'طلب الاستجابة غير صالح.',
        'payment-cancelled'   => 'تم إلغاء الدفع.',
        'payment-failed'      => 'فشل الدفع. يرجى المحاولة مجدداً.',
        'payment-success'     => 'تم الدفع بنجاح.',
        'provide-credentials' => 'مفتاح API الخاص بـ UPayments غير مضبوط. يرجى التواصل مع الدعم.',
        'redirect-failed'     => 'تعذر بدء عملية الدفع. يرجى المحاولة مجدداً.',
        'verification-failed' => 'فشل التحقق من الدفع.',
    ],
];
