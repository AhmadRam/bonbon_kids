<?php

namespace Webkul\UPayments\Payment;

use Webkul\Payment\Payment\Payment;

abstract class UPayments extends Payment
{
    /**
     * Gateway source identifier sent to UPayments API.
     * Each subclass defines its own value.
     */
    protected string $gatewaySource;

    /**
     * Format a currency value to 3 decimal places (KWD standard).
     */
    public function formatAmount(float|int $number): float
    {
        return round((float) $number, 3);
    }

    /**
     * Strip non-numeric characters from a phone number.
     */
    public function formatPhone(mixed $phone): string
    {
        return preg_replace('/[^0-9]/', '', (string) $phone);
    }

    /**
     * Get the gateway source identifier (knet, cc, apple-pay, etc.).
     */
    public function getGatewaySource(): string
    {
        return $this->gatewaySource;
    }

    /**
     * Get API key based on sandbox mode.
     */
    public function getApiKey(): ?string
    {
        $sandbox = core()->getConfigData('sales.payment_methods.upayments.sandbox');

        return $sandbox
            ? core()->getConfigData('sales.payment_methods.upayments.api_test_key')
            : core()->getConfigData('sales.payment_methods.upayments.api_key');
    }

    /**
     * Get base API URL based on sandbox mode.
     */
    public function getBaseUrl(): string
    {
        $sandbox = core()->getConfigData('sales.payment_methods.upayments.sandbox');

        return $sandbox
            ? 'https://sandboxapi.upayments.com/api/v1'
            : 'https://apiv2api.upayments.com/api/v1';
    }

    /**
     * Check if API key is configured.
     */
    public function hasValidCredentials(): bool
    {
        return ! empty($this->getApiKey());
    }

    /**
     * Override isAvailable to also check credentials.
     */
    public function isAvailable(): bool
    {
        return parent::isAvailable() && $this->hasValidCredentials();
    }

    /**
     * Create a charge on UPayments and return the payment redirect link.
     *
     * @return string The hosted payment page URL.
     *
     * @throws \Exception
     */
    public function createCharge(\Webkul\Checkout\Contracts\Cart $cart): string
    {
        $billingAddress = $cart->billing_address;

        $payload = [
            'paymentGateway' => [
                'src' => $this->gatewaySource,
            ],
            'reference' => [
                'id' => 'cart-' . $cart->id . '-' . time(),
            ],
            'order' => [
                'id'          => (string) $cart->id,
                'amount'      => $this->formatAmount($cart->grand_total),
                'currency'    => $cart->cart_currency_code ?? 'KWD',
                'description' => 'Order from cart #' . $cart->id,
            ],
            'customer' => [
                'name'   => trim(($billingAddress->first_name ?? '') . ' ' . ($billingAddress->last_name ?? '')),
                'email'  => $billingAddress->email ?? $cart->customer_email ?? '',
                'mobile' => $this->formatPhone($billingAddress->phone ?? ''),
            ],
            'returnUrl'       => route('upayments.payment.callback'),
            'cancelUrl'       => route('upayments.payment.cancel'),
            'notificationUrl' => route('upayments.payment.webhook'),
            'language'        => app()->getLocale() === 'ar' ? 'ar' : 'en',
        ];

        $response = \Illuminate\Support\Facades\Http::withToken($this->getApiKey())
            ->acceptJson()
            ->post($this->getBaseUrl() . '/charge', $payload);

        if (! $response->successful()) {
            throw new \Exception('UPayments API error: ' . $response->body());
        }

        $json = $response->json();

        if (! isset($json['data']['link'])) {
            throw new \Exception('UPayments: no payment link in response. Body: ' . $response->body());
        }

        return $json['data']['link'];
    }

    /**
     * Verify payment status using UPayments track_id.
     * Returns true if payment was CAPTURED (successful).
     */
    public function verifyPayment(string $trackId): bool
    {
        $response = \Illuminate\Support\Facades\Http::withToken($this->getApiKey())
            ->acceptJson()
            ->get($this->getBaseUrl() . '/get-payment-status/' . $trackId);

        if (! $response->successful()) {
            return false;
        }

        $json = $response->json();

        return isset($json['data']['transaction']['result'])
            && strtoupper($json['data']['transaction']['result']) === 'CAPTURED';
    }
}
