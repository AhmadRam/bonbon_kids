<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Checkout\Models\CartItem;
use Webkul\Checkout\Models\CartPayment;
use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Daftra\Http\Controllers\DaftraController;
use Webkul\Daftra\Jobs\SyncOrderToDaftraJob;
use Webkul\Daftra\Listeners\OrderListener;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

use function Pest\Laravel\postJson;

function createReadyCheckoutCart(): Cart
{
    $product = (new ProductFaker([
        'attributes' => [
            5 => 'new',
            26 => 'guest_checkout',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],

            'guest_checkout' => [
                'boolean_value' => true,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $cart = Cart::factory()->create(['shipping_method' => 'free_free']);

    $additional = [
        'product_id' => $product->id,
        'rating' => '0',
        'is_buy_now' => '0',
        'quantity' => '1',
    ];

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'sku' => $product->sku,
        'quantity' => $additional['quantity'],
        'name' => $product->name,
        'price' => $convertedPrice = core()->convertPrice($price = $product->price),
        'price_incl_tax' => $convertedPrice,
        'base_price' => $price,
        'base_price_incl_tax' => $price,
        'total' => $total = $convertedPrice * $additional['quantity'],
        'total_incl_tax' => $total,
        'base_total' => $price * $additional['quantity'],
        'weight' => $product->weight ?? 0,
        'total_weight' => ($product->weight ?? 0) * $additional['quantity'],
        'base_total_weight' => ($product->weight ?? 0) * $additional['quantity'],
        'type' => $product->type,
        'additional' => $additional,
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'address_type' => CartAddress::ADDRESS_TYPE_BILLING,
    ]);

    $cartShippingAddress = CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'address_type' => CartAddress::ADDRESS_TYPE_SHIPPING,
    ]);

    CartPayment::factory()->create([
        'cart_id' => $cart->id,
        'method' => $paymentMethod = 'cashondelivery',
        'method_title' => core()->getConfigData('sales.payment_methods.'.$paymentMethod.'.title'),
    ]);

    CartShippingRate::factory()->create([
        'carrier' => 'free',
        'carrier_title' => 'Free shipping',
        'method' => 'free_free',
        'method_title' => 'Free Shipping',
        'method_description' => 'Free Shipping',
        'cart_address_id' => $cartShippingAddress->id,
        'cart_id' => $cart->id,
    ]);

    cart()->setCart($cart);
    cart()->collectTotals();

    return $cart;
}

it('prevents OrderRepository from creating duplicate orders for the same cart_id', function () {
    Mail::fake();

    $cart = createReadyCheckoutCart();
    $data = (new OrderResource($cart))->jsonSerialize();

    /** @var OrderRepository $orderRepository */
    $orderRepository = app(OrderRepository::class);

    $firstOrder = $orderRepository->create($data);
    $secondOrder = $orderRepository->create($data);

    expect($secondOrder->id)->toBe($firstOrder->id);
    expect(Order::where('cart_id', $cart->id)->count())->toBe(1);
});

it('redirects to success without creating a duplicate order when storeOrder is called for an already ordered cart', function () {
    Mail::fake();

    $cart = createReadyCheckoutCart();

    postJson(route('shop.checkout.onepage.orders.store'))
        ->assertOk()
        ->assertJsonPath('data.redirect', true)
        ->assertJsonPath('data.redirect_url', route('shop.checkout.onepage.success'));

    expect(Order::where('cart_id', $cart->id)->count())->toBe(1);

    // Simulate a duplicate concurrent client request still holding the cart reference before deactivation.
    $cart->refresh();
    $cart->update(['is_active' => true]);
    cart()->setCart($cart);

    postJson(route('shop.checkout.onepage.orders.store'))
        ->assertOk()
        ->assertJsonPath('data.redirect', true)
        ->assertJsonPath('data.redirect_url', route('shop.checkout.onepage.success'));

    expect(Order::where('cart_id', $cart->id)->count())->toBe(1);
});

it('queues Daftra sync job after commit and skips orders already synced to Daftra', function () {
    Bus::fake([SyncOrderToDaftraJob::class]);
    config(['daftra.api_key' => 'test-daftra-api-key']);

    $order = Order::factory()->create([
        'daftra_invoice_id' => '999',
    ]);

    Event::dispatch('checkout.order.save.after', $order);

    Bus::assertDispatched(SyncOrderToDaftraJob::class, function (SyncOrderToDaftraJob $job) use ($order) {
        return $job->orderId === $order->id;
    });

    $daftraMock = $this->mock(DaftraController::class);
    $daftraMock->shouldNotReceive('getClientsByEmail');
    $daftraMock->shouldNotReceive('getClients');
    $daftraMock->shouldNotReceive('createClient');
    $daftraMock->shouldNotReceive('createInvoice');

    Cache::flush();

    app(OrderListener::class)->syncOrderToDaftra($order);
});
