<?php

namespace Webkul\UPayments\Payment;

use Illuminate\Support\Facades\Storage;

class UPaymentsGooglePay extends UPayments
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'upayments_google_pay';

    /**
     * Gateway source sent to UPayments API.
     */
    protected string $gatewaySource = 'google-pay';

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

        return $url ? Storage::url($url) : bagisto_asset('images/google-pay.png', 'shop');
    }
}
