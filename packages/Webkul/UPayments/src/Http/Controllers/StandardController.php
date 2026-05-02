<?php

namespace Webkul\UPayments\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;
use Webkul\Sales\Transformers\OrderResource;

class StandardController extends Controller
{
    /**
     * Map of payment method codes to their gateway source.
     */
    protected array $methodSourceMap = [
        'upayments_knet'        => 'knet',
        'upayments_cc'          => 'cc',
        'upayments_apple_pay'   => 'apple-pay',
        'upayments_google_pay'  => 'google-pay',
        'upayments_samsung_pay' => 'samsung-pay',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
        protected OrderTransactionRepository $orderTransactionRepository,
    ) {}

    /**
     * Redirect to UPayments hosted payment page.
     */
    public function redirect(): RedirectResponse
    {
        $method = request()->get('paymentMethod');
        $cart = Cart::getCart();

        if (! $cart) {
            session()->flash('error', trans('upayments::app.response.cart-not-found'));

            return redirect()->route('shop.checkout.cart.index');
        }

        if (! isset($this->methodSourceMap[$method])) {
            session()->flash('error', trans('upayments::app.response.invalid-method'));

            return redirect()->route('shop.checkout.cart.index');
        }

        $paymentClass = Config::get('payment_methods.' . $method . '.class');

        if (! $paymentClass) {
            session()->flash('error', trans('upayments::app.response.invalid-method'));

            return redirect()->route('shop.checkout.cart.index');
        }

        /** @var \Webkul\UPayments\Payment\UPayments $gateway */
        $gateway = app($paymentClass);

        if (! $gateway->hasValidCredentials()) {
            session()->flash('error', trans('upayments::app.response.provide-credentials'));

            return redirect()->route('shop.checkout.cart.index');
        }

        try {
            $link = $gateway->createCharge($cart);

            // Store cart ID and method in session for callback verification
            session()->put('upayments_cart_id', $cart->id);
            session()->put('upayments_method', $method);

            Log::info('UPayments: redirect initiated', [
                'cart_id' => $cart->id,
                'method'  => $method,
                'link'    => $link,
            ]);

            return redirect($link);
        } catch (\Exception $e) {
            Log::error('UPayments: redirect failed', [
                'cart_id' => $cart->id,
                'method'  => $method,
                'error'   => $e->getMessage(),
            ]);

            session()->flash('error', trans('upayments::app.response.redirect-failed') . ': ' . $e->getMessage());

            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle UPayments callback after payment.
     */
    public function callback(Request $request): RedirectResponse
    {
        $trackId = $request->get('trackId') ?? $request->get('track_id') ?? $request->get('id');

        Log::info('UPayments: callback received', $request->all());

        if (! $trackId) {
            session()->flash('error', trans('upayments::app.response.invalid-request'));

            return redirect()->route('shop.checkout.cart.index');
        }

        $cartId = session()->get('upayments_cart_id');
        $method = session()->get('upayments_method');

        if (! $cartId || ! $method) {
            session()->flash('error', trans('upayments::app.response.cart-not-found'));

            return redirect()->route('shop.checkout.cart.index');
        }

        $paymentClass = Config::get('payment_methods.' . $method . '.class');

        /** @var \Webkul\UPayments\Payment\UPayments $gateway */
        $gateway = app($paymentClass);

        try {
            $isPaid = $gateway->verifyPayment($trackId);

            if (! $isPaid) {
                Log::warning('UPayments: payment not captured', [
                    'track_id' => $trackId,
                    'cart_id'  => $cartId,
                ]);

                session()->flash('error', trans('upayments::app.response.payment-failed'));

                return redirect()->route('shop.checkout.cart.index');
            }

            $cart = $this->cartRepository->find($cartId) ?? Cart::getCart();

            if (! $cart || ! $cart->is_active) {
                session()->flash('error', trans('upayments::app.response.cart-processed'));

                return redirect()->route('shop.checkout.cart.index');
            }

            Cart::setCart($cart);
            Cart::collectTotals();

            $data = (new OrderResource($cart))->jsonSerialize();

            $data['payment']['additional'] = [
                'upayments_track_id' => $trackId,
                'upayments_method'   => $method,
            ];

            $order = $this->orderRepository->create($data);

            $this->orderRepository->update(['status' => 'processing'], $order->id);

            Log::info('UPayments: order created', [
                'order_id' => $order->id,
                'track_id' => $trackId,
            ]);

            if ($order->canInvoice()) {
                $invoice = $this->invoiceRepository->create($this->prepareInvoiceData($order));

                $this->orderTransactionRepository->create([
                    'transaction_id' => $trackId,
                    'status'         => 'paid',
                    'type'           => $order->payment->method,
                    'payment_method' => $order->payment->method,
                    'order_id'       => $order->id,
                    'invoice_id'     => $invoice->id,
                    'amount'         => $order->base_grand_total,
                    'data'           => json_encode([
                        'upayments_track_id' => $trackId,
                        'upayments_method'   => $method,
                    ]),
                ]);
            }

            Cart::deActivateCart();

            session()->forget(['upayments_cart_id', 'upayments_method']);

            session()->flash('order', $order);

            session()->flash('success', trans('upayments::app.response.payment-success'));

            return redirect()->route('shop.checkout.onepage.success');
        } catch (\Exception $e) {
            Log::error('UPayments: callback processing failed', [
                'track_id' => $trackId,
                'error'    => $e->getMessage(),
            ]);

            session()->flash('error', trans('upayments::app.response.verification-failed') . ': ' . $e->getMessage());

            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle payment cancellation.
     */
    public function cancel(): RedirectResponse
    {
        Log::info('UPayments: payment cancelled', [
            'cart_id' => session()->get('upayments_cart_id'),
        ]);

        session()->forget(['upayments_cart_id', 'upayments_method']);

        session()->flash('error', trans('upayments::app.response.payment-cancelled'));

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Handle UPayments server-to-server webhook notification.
     */
    public function webhook(Request $request)
    {
        $payload = $request->json()->all();

        Log::info('UPayments: webhook received', $payload);

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Prepare invoice data from order.
     */
    protected function prepareInvoiceData(\Webkul\Sales\Models\Order $order): array
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
