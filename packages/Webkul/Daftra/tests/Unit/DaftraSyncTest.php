<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Webkul\Daftra\Http\Controllers\DaftraController;
use Webkul\Daftra\Listeners\OrderListener;
use Webkul\Daftra\Services\CheckoutStockVerifier;
use Webkul\Product\Helpers\Indexers\Inventory;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Sales\Jobs\CreateDaftraOrderRefund;
use Webkul\Sales\Repositories\OrderRepository;

uses(TestCase::class);

beforeEach(function () {
    config([
        'cache.default' => 'array',
        'daftra.base_url' => 'https://z-toys.daftra.com',
        'daftra.client_secret' => 'test-secret',
        'daftra.api_key' => 'test-api-key',
        'daftra.store_id' => 2,
        'daftra.invoice_layout_id' => 13,
    ]);

    Cache::flush();
    Cache::put('daftra_access_token', 'test-token', 3600);
});

it('finds a product by exact product_code via direct query and caches the result', function () {
    Http::fake([
        'https://z-toys.daftra.com/api2/products.json*' => Http::response([
            'data' => [
                [
                    'Product' => [
                        'id' => '721',
                        'product_code' => 'YG9-70',
                        'name' => 'كرات LED المضيئة الملونة',
                    ],
                ],
            ],
        ], 200),
    ]);

    $controller = app(DaftraController::class);

    $first = $controller->findProductBySku(' yg9-70 ', 'Fallback Name');
    $second = $controller->findProductBySku('YG9-70', 'Fallback Name');

    expect($first)->toBe([
        'id' => 721,
        'product_code' => 'YG9-70',
        'name' => 'كرات LED المضيئة الملونة',
    ]);
    expect($second)->toBe($first);

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/api2/products.json')
            && $request['product_code'] === 'yg9-70';
    });
});

it('falls back to cached catalog for compound codes, prefixed codes, barcodes, and unique names', function () {
    $catalogResponse = [
        'data' => [
            [
                'Product' => [
                    'id' => '741',
                    'product_code' => '  2049  -  2029',
                    'barcode' => '3170000000134',
                    'name' => 'مظلة الأطفال المرحة',
                ],
            ],
            [
                'Product' => [
                    'id' => '1466',
                    'product_code' => ' SKU - 936-2',
                    'barcode' => '',
                    'name' => 'لعبة سيارة',
                ],
            ],
            [
                'Product' => [
                    'id' => '500',
                    'product_code' => 'OTHER-1',
                    'barcode' => '999888777',
                    'name' => 'منتج فريد',
                ],
            ],
            [
                'Product' => [
                    'id' => '601',
                    'product_code' => 'DUP-1',
                    'barcode' => '',
                    'name' => 'اسم مكرر',
                ],
            ],
            [
                'Product' => [
                    'id' => '602',
                    'product_code' => 'DUP-2',
                    'barcode' => '',
                    'name' => 'اسم مكرر',
                ],
            ],
        ],
        'pagination' => [
            'page' => 1,
            'page_count' => 1,
        ],
    ];

    Http::fake(function (Request $request) use ($catalogResponse) {
        if (isset($request['product_code'])) {
            return Http::response(['data' => []], 200);
        }

        return Http::response($catalogResponse, 200);
    });

    $controller = app(DaftraController::class);

    expect($controller->findProductBySku('2029'))->toBe([
        'id' => 741,
        'product_code' => '2049  -  2029',
        'name' => 'مظلة الأطفال المرحة',
    ]);

    expect($controller->findProductBySku('936-2'))->toBe([
        'id' => 1466,
        'product_code' => 'SKU - 936-2',
        'name' => 'لعبة سيارة',
    ]);

    expect($controller->findProductBySku('999888777'))->toBe([
        'id' => 500,
        'product_code' => 'OTHER-1',
        'name' => 'منتج فريد',
    ]);

    expect($controller->findProductBySku('MISSING-SKU', 'منتج فريد'))->toBe([
        'id' => 500,
        'product_code' => 'OTHER-1',
        'name' => 'منتج فريد',
    ]);

    expect($controller->findProductBySku('MISSING-SKU', 'اسم مكرر'))->toBeNull();

    expect($controller->findProductBySku('NON-EXISTENT', 'غير موجود'))->toBeNull();
});

it('syncs order to daftra with resolved product_id, custom layout columns, and header shipping_amount', function () {
    Http::fake(function (Request $request) {
        $url = $request->url();

        if (str_contains($url, '/api2/clients.json')) {
            return Http::response([
                'data' => [
                    [
                        'Client' => [
                            'id' => '77',
                            'email' => 'abcaba746@gmail.com',
                        ],
                    ],
                ],
            ], 200);
        }

        if (str_contains($url, '/api2/products.json')) {
            if (($request['product_code'] ?? null) === 'YG9-70') {
                return Http::response([
                    'data' => [
                        [
                            'Product' => [
                                'id' => '721',
                                'product_code' => 'YG9-70',
                                'name' => 'كرات LED المضيئة الملونة',
                            ],
                        ],
                    ],
                ], 200);
            }

            return Http::response([
                'data' => [],
                'pagination' => ['page' => 1, 'page_count' => 1],
            ], 200);
        }

        if (str_contains($url, '/api2/invoices') && $request->method() === 'POST') {
            return Http::response(['id' => 999], 200);
        }

        return Http::response([], 404);
    });

    $order = new class
    {
        public int $id = 13;

        public string $status = 'processing';

        public string $order_currency_code = 'KWD';

        public float $shipping_amount = 1.0;

        public string $shipping_title = 'توصيل قياسي';

        public float $grand_total = 7.64;

        public string $customer_email = 'abcaba746@gmail.com';

        public string $customer_first_name = 'رتاج';

        public string $customer_last_name = 'العنزي';

        public object $billing_address;

        public object $payment;

        public mixed $created_at;

        public array $items = [];

        public ?int $daftra_invoice_id = null;

        public bool $saved = false;

        public function __construct()
        {
            $this->billing_address = (object) [
                'phone' => '+96566832382',
                'address1' => '',
                'city' => 'سعد العبد الله',
                'country' => 'KW',
            ];
            $this->payment = (object) ['method' => 'cashondelivery'];
            $this->created_at = now();
            $this->items = [
                (object) [
                    'child' => (object) ['sku' => 'YG9-70'],
                    'sku' => 'PARENT-SKU',
                    'name' => 'كرات LED',
                    'price' => 0.75,
                    'qty_ordered' => 1,
                    'discount_amount' => 0,
                ],
                (object) [
                    'child' => null,
                    'sku' => 'NO-MATCH',
                    'name' => 'منتج غير معروف',
                    'price' => 2.50,
                    'qty_ordered' => 2,
                    'discount_amount' => 0.5,
                ],
            ];
        }

        public function loadMissing(string $relation): self
        {
            return $this;
        }

        public function refresh(): self
        {
            return $this;
        }

        public function save(): bool
        {
            $this->saved = true;

            return true;
        }
    };

    $listener = new OrderListener(
        app(DaftraController::class),
        Mockery::mock(OrderRepository::class)
    );

    $listener->syncOrderToDaftra($order);

    expect($order->daftra_invoice_id)->toBe(999);
    expect($order->saved)->toBeTrue();

    Http::assertSent(function (Request $request) {
        if (! str_contains($request->url(), '/api2/invoices') || $request->method() !== 'POST') {
            return false;
        }

        $payload = $request->data();
        $invoice = $payload['Invoice'] ?? [];
        $items = $payload['InvoiceItem'] ?? [];

        return ($invoice['invoice_layout_id'] ?? null) === 13
            && ($invoice['store_id'] ?? null) === 2
            && ($invoice['shipping_amount'] ?? null) === 1.0
            && count($items) === 2
            && $items[0]['item'] === '1'
            && $items[0]['description'] === 'YG9-70'
            && $items[0]['col_3'] === 'كرات LED المضيئة الملونة'
            && $items[0]['product_id'] === 721
            && $items[0]['store_id'] === 2
            && $items[1]['item'] === '2'
            && $items[1]['description'] === 'NO-MATCH'
            && $items[1]['col_3'] === 'منتج غير معروف'
            && $items[1]['product_id'] === null
            && $items[1]['store_id'] === 2;
    });
});

it('syncs upayments_knet order with empty Payment in invoice and records invoice_payments separately without failing invoice sync on payment error', function () {
    Http::fake(function (Request $request) {
        $url = $request->url();

        if (str_contains($url, '/api2/clients.json')) {
            return Http::response([
                'data' => [
                    [
                        'Client' => [
                            'id' => '88',
                            'email' => 'knet@example.com',
                        ],
                    ],
                ],
            ], 200);
        }

        if (str_contains($url, '/api2/products.json')) {
            return Http::response([
                'data' => [
                    [
                        'Product' => [
                            'id' => '1319',
                            'product_code' => '6161-366',
                            'name' => 'دمية الموضة',
                        ],
                    ],
                ],
            ], 200);
        }

        if (str_contains($url, '/api2/invoices') && $request->method() === 'POST') {
            return Http::response(['id' => 777], 200);
        }

        if (str_contains($url, '/api2/invoice_payments') && $request->method() === 'POST') {
            return Http::response(['result' => 'failed', 'message' => 'Validation error'], 422);
        }

        return Http::response([], 404);
    });

    $order = new class
    {
        public int $id = 26;

        public string $status = 'processing';

        public string $order_currency_code = 'KWD';

        public float $shipping_amount = 1.0;

        public float $grand_total = 13.59;

        public string $customer_email = 'knet@example.com';

        public string $customer_first_name = 'شيماء';

        public string $customer_last_name = 'الخالد';

        public object $billing_address;

        public object $payment;

        public mixed $created_at;

        public array $items = [];

        public ?int $daftra_invoice_id = null;

        public bool $saved = false;

        public function __construct()
        {
            $this->billing_address = (object) [
                'phone' => '+96550313111',
                'address1' => '',
                'city' => 'سعد العبد الله',
                'country' => 'KW',
            ];
            $this->payment = (object) ['method' => 'upayments_knet'];
            $this->created_at = now();
            $this->items = [
                (object) [
                    'child' => null,
                    'sku' => '6161-366',
                    'name' => 'دمية الموضة',
                    'price' => 0.99,
                    'qty_ordered' => 1,
                    'discount_amount' => 0,
                ],
            ];
        }

        public function loadMissing(string $relation): self
        {
            return $this;
        }

        public function refresh(): self
        {
            return $this;
        }

        public function save(): bool
        {
            $this->saved = true;

            return true;
        }
    };

    $listener = new OrderListener(
        app(DaftraController::class),
        Mockery::mock(OrderRepository::class)
    );

    $listener->syncOrderToDaftra($order);

    expect($order->daftra_invoice_id)->toBe(777);
    expect($order->saved)->toBeTrue();

    Http::assertSent(function (Request $request) {
        if (! str_contains($request->url(), '/api2/invoices') || $request->method() !== 'POST') {
            return false;
        }

        $payload = $request->data();

        return $payload['Payment'] === []
            && ($payload['Invoice']['store_id'] ?? null) === 2;
    });

    Http::assertSent(function (Request $request) {
        if (! str_contains($request->url(), '/api2/invoice_payments') || $request->method() !== 'POST') {
            return false;
        }

        $payment = $request->data()['InvoicePayment'] ?? [];

        return ($payment['invoice_id'] ?? null) === 777
            && ($payment['treasury_id'] ?? null) === 3
            && ($payment['payment_method'] ?? null) === 'cash_on_delivery'
            && ($payment['transaction_id'] ?? null) === 'order_26';
    });
});

it('extracts normalized SKU candidates from prefixed and compound Daftra product codes', function () {
    $controller = app(DaftraController::class);

    expect($controller->extractSkuCandidates(' SKU - 936-2', ''))->toContain('936-2');
    expect($controller->extractSkuCandidates('  2049  -  2029', '3170000000134'))
        ->toContain('2049')
        ->toContain('2029')
        ->toContain('3170000000134');
});

it('fetches live Sharq store balance only, micro-caches it, and fails open on errors', function () {
    Http::fake(function (Request $request) {
        if ($request['product_code'] === 'Y209') {
            return Http::response([
                'data' => [
                    [
                        'Product' => [
                            'id' => '742',
                            'product_code' => 'Y209',
                            'stock_balance' => '105',
                            'deactivate' => '0',
                            'ProductStock' => [
                                ['store_id' => '1', 'balance' => '105'],
                                ['store_id' => '2', 'balance' => '0'],
                            ],
                        ],
                    ],
                ],
            ], 200);
        }

        if ($request['product_code'] === '6161-366') {
            return Http::response([
                'data' => [
                    [
                        'Product' => [
                            'id' => '1319',
                            'product_code' => '6161-366',
                            'stock_balance' => '4',
                            'deactivate' => '0',
                            'ProductStock' => [
                                ['store_id' => '1', 'balance' => '0'],
                                ['store_id' => '2', 'balance' => '4'],
                            ],
                        ],
                    ],
                ],
            ], 200);
        }

        return Http::response(['error' => 'Gateway timeout'], 504);
    });

    $controller = app(DaftraController::class);

    $first = $controller->getLiveStoreBalances([
        ['sku' => 'Y209'],
        ['sku' => '6161-366'],
        ['sku' => 'TIMEOUT-SKU'],
    ], 2, 2.5, 1.5);

    expect($first['Y209'])->toBe(0.0);
    expect($first['6161-366'])->toBe(4.0);
    expect($first['TIMEOUT-SKU'])->toBeNull();

    $second = $controller->getLiveStoreBalances([
        ['sku' => 'Y209'],
        ['sku' => '6161-366'],
    ], 2, 2.5, 1.5);

    expect($second['Y209'])->toBe(0.0);
    expect($second['6161-366'])->toBe(4.0);

    // Y209 and 6161-366 served from cache on second call (only 3 total HTTP calls from first batch).
    Http::assertSentCount(3);
});

it('CheckoutStockVerifier rejects checkout when Sharq stock is insufficient and fails open on API error', function () {
    config(['daftra.checkout_stock_check' => true]);

    $daftraMock = Mockery::mock(DaftraController::class);
    $daftraMock->shouldReceive('getLiveStoreBalances')
        ->once()
        ->andReturn(['6161-366' => 0.0]);
    $daftraMock->shouldReceive('forgetLiveStockCache')
        ->once()
        ->with(['6161-366'], 2);

    $invRepoMock = Mockery::mock(ProductInventoryRepository::class);
    $invRepoMock->shouldReceive('saveInventories')->never();

    $indexerMock = Mockery::mock(Inventory::class);
    $productRepoMock = Mockery::mock(ProductRepository::class);

    DB::shouldReceive('table')->with('order_items')->andReturnSelf();
    DB::shouldReceive('join')->andReturnSelf();
    DB::shouldReceive('leftJoin')->andReturnSelf();
    DB::shouldReceive('whereNull')->andReturnSelf();
    DB::shouldReceive('whereNotIn')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('whereIn')->andReturnSelf();
    DB::shouldReceive('whereNotNull')->andReturnSelf();
    DB::shouldReceive('selectRaw')->andReturnSelf();
    DB::shouldReceive('groupBy')->andReturnSelf();
    DB::shouldReceive('get')->andReturn(collect());
    DB::shouldReceive('table')->with('inventory_sources')->andReturnSelf();
    DB::shouldReceive('exists')->andReturn(false);

    $verifier = new CheckoutStockVerifier(
        $daftraMock,
        $invRepoMock,
        $indexerMock,
        $productRepoMock
    );

    $product = new class
    {
        public int $id = 52;

        public string $sku = '6161-366';

        public string $name = 'دمية الموضة';

        public bool $manage_stock = true;

        public function isStockable(): bool
        {
            return true;
        }
    };

    $cart = (object) [
        'items' => [
            (object) [
                'type' => 'simple',
                'quantity' => 1,
                'name' => 'دمية الموضة',
                'product' => $product,
                'child' => null,
            ],
        ],
    ];

    $shortages = $verifier->verifyCart($cart);

    expect($shortages)->toHaveCount(1);
    expect($shortages[0])->toMatchArray([
        'product_id' => 52,
        'sku' => '6161-366',
        'requested' => 1,
        'available' => 0,
    ]);
    expect($verifier->formatShortageMessage($shortages))->toContain('دمية الموضة');

    // Now test fail-open when Daftra throws
    $failingDaftraMock = Mockery::mock(DaftraController::class);
    $failingDaftraMock->shouldReceive('getLiveStoreBalances')
        ->once()
        ->andThrow(new RuntimeException('Daftra unreachable'));

    $failOpenVerifier = new CheckoutStockVerifier(
        $failingDaftraMock,
        $invRepoMock,
        $indexerMock,
        $productRepoMock
    );

    expect($failOpenVerifier->verifyCart($cart))->toBe([]);
});

it('syncs refund to daftra with resolved product_id, custom layout columns, and header shipping_amount', function () {
    Http::fake(function (Request $request) {
        $url = $request->url();

        if (str_contains($url, '/api2/products.json')) {
            return Http::response([
                'data' => [
                    [
                        'Product' => [
                            'id' => '721',
                            'product_code' => 'YG9-70',
                            'name' => 'كرات LED المضيئة الملونة',
                        ],
                    ],
                ],
            ], 200);
        }

        if (str_contains($url, '/api2/refund_receipts') && $request->method() === 'POST') {
            return Http::response(['id' => 888], 200);
        }

        return Http::response([], 404);
    });

    $order = (object) [
        'id' => 13,
        'increment_id' => '12',
        'daftra_invoice_id' => 612,
        'order_currency_code' => 'KWD',
        'customer_email' => 'abcaba746@gmail.com',
        'payment' => (object) ['method' => 'cashondelivery'],
    ];

    $refund = new class
    {
        public int $id = 55;

        public ?int $inventory_source_id = 2;

        public float $base_shipping_amount = 1.0;

        public float $base_adjustment_refund = 0.0;

        public float $base_grand_total = 1.75;

        public mixed $inventory_source = null;

        public array $items = [];

        public ?int $daftra_refund_id = null;

        public bool $saved = false;

        public function __construct()
        {
            $this->inventory_source = (object) ['name' => 'Sub Warehouse'];
            $this->items = [
                (object) [
                    'child' => null,
                    'order_item' => (object) ['child' => (object) ['sku' => 'YG9-70']],
                    'sku' => 'PARENT-SKU',
                    'name' => 'كرات LED',
                    'base_price' => 0.75,
                    'qty' => 1,
                    'base_discount_amount' => 0,
                    'base_tax_amount' => 0,
                ],
                (object) [
                    'child' => null,
                    'order_item' => null,
                    'sku' => 'SKIP-ME',
                    'name' => 'Ignored Zero Qty',
                    'base_price' => 2.0,
                    'qty' => 0,
                    'base_discount_amount' => 0,
                    'base_tax_amount' => 0,
                ],
            ];
        }

        public function save(): bool
        {
            $this->saved = true;

            return true;
        }
    };

    $address = (object) [
        'first_name' => 'رتاج',
        'last_name' => 'العنزي',
        'email' => 'abcaba746@gmail.com',
        'address1' => 'Street 1',
        'address2' => '',
        'postcode' => '',
        'city' => 'سعد العبد الله',
        'state' => 'jahra',
        'country' => 'KW',
    ];

    $job = new class($order->id, $refund->id) extends CreateDaftraOrderRefund
    {
        public function executeRefund($order, $refund, $address, int $clientId, DaftraController $service)
        {
            $this->daftraService = $service;

            return $this->createRefund($order, $refund, $address, $clientId);
        }
    };

    $job->executeRefund($order, $refund, $address, 77, app(DaftraController::class));

    expect($refund->daftra_refund_id)->toBe(888);
    expect($refund->saved)->toBeTrue();

    Http::assertSent(function (Request $request) {
        if (! str_contains($request->url(), '/api2/refund_receipts') || $request->method() !== 'POST') {
            return false;
        }

        $payload = $request->data();
        $receipt = $payload['RefundReceipt'] ?? [];
        $items = $payload['InvoiceItem'] ?? [];

        return ($receipt['invoice_layout_id'] ?? null) === 13
            && ($receipt['store_id'] ?? null) === 2
            && ($receipt['shipping_amount'] ?? null) === 1.0
            && count($items) === 1
            && $items[0]['item'] === '1'
            && $items[0]['description'] === 'YG9-70'
            && $items[0]['col_3'] === 'كرات LED المضيئة الملونة'
            && $items[0]['product_id'] === 721
            && $items[0]['store_id'] === 2;
    });
});
