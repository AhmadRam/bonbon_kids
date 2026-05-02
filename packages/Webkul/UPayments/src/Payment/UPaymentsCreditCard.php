<?php

namespace Webkul\UPayments\Payment;

use Illuminate\Support\Facades\Storage;

class UPaymentsCreditCard extends UPayments
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'upayments_cc';

    /**
     * Gateway source sent to UPayments API.
     */
    protected string $gatewaySource = 'cc';

    /**
     * Get redirect URL.
     */
    public function getRedirectUrl(): string
    {
        return route('upayments.standard.redirect').'?paymentMethod='.$this->code;
    }

    /**
     * Get payment method image.
     */
    public function getImage(): string
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/cc.png', 'shop');
    }
}
