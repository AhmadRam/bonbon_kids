<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Coupon Restrictions
    |--------------------------------------------------------------------------
    |
    | When enabled (true), all existing and newly created coupons will not apply
    | to products in excluded categories ("ألعاب أقل من دينار", "عروض وخصومات")
    | or products that have an active special price.
    |
    | Can be toggled with a single variable in .env:
    | COUPON_DEFAULT_RESTRICTIONS=true / false
    |
    */
    'default_restrictions' => env('COUPON_DEFAULT_RESTRICTIONS', true),

    'excluded_categories' => [
        'under-1-dinar',
        'offers-discounts',
    ],
];
