<?php

namespace Webkul\Daftra\Http\Controllers;

use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DaftraController extends Controller
{
    private $baseUrl;

    private $clientId;

    private $clientSecret;

    private $username;

    private $password;

    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('daftra.base_url');
        $this->clientId = config('daftra.client_id');
        $this->clientSecret = config('daftra.client_secret');
        $this->username = config('daftra.username');
        $this->password = config('daftra.password');
        $this->apiKey = config('daftra.api_key');
    }

    /**
     * احصل على Access Token
     */
    public function getAccessToken(?float $timeout = null, ?float $connectTimeout = null)
    {
        // تحقق من وجود التوكن في الكاش
        $cachedToken = Cache::get('daftra_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $request = Http::asForm();

            if ($connectTimeout !== null) {
                $request = $request->connectTimeout($connectTimeout);
            }

            if ($timeout !== null) {
                $request = $request->timeout($timeout);
            }

            $response = $request->post($this->baseUrl.'/v2/oauth/token', [
                'client_secret' => $this->clientSecret,
                'client_id' => $this->clientId,
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'];
                $expiresIn = max(60, ($data['expires_in'] ?? 3600) - 300); // أقل بـ 5 دقائق للأمان

                // احفظ التوكن في الكاش
                Cache::put('daftra_access_token', $accessToken, $expiresIn);

                return $accessToken;
            }

            throw new Exception('فشل في الحصول على Access Token: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في الاتصال بـ Daftra API: '.$e->getMessage());
        }
    }

    /**
     * إعداد الهيدرز المطلوبة للطلبات
     */
    private function getHeaders(?float $timeout = null, ?float $connectTimeout = null)
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->getAccessToken($timeout, $connectTimeout),
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * احصل على جميع المنتجات
     */
    public function getProducts($page = 1, $limit = 1000)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl.'/api2/products', [
                    'page' => $page,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                return $response->json()['data'];
            }

            throw new Exception('فشل في جلب المنتجات: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في جلب المنتجات: '.$e->getMessage());
        }
    }

    /**
     * Search for a product in Daftra by SKU (product_code), barcode, or unique name.
     */
    public function findProductBySku(?string $sku, ?string $fallbackName = null): ?array
    {
        $sku = trim((string) $sku);
        $fallbackName = trim((string) $fallbackName);

        if ($sku === '' && $fallbackName === '') {
            return null;
        }

        $cacheKey = 'daftra_product_lookup_'.md5(mb_strtolower($sku.'|'.$fallbackName));
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && ! empty($cached['id'])) {
            return $cached;
        }

        try {
            if ($sku !== '') {
                $response = Http::withHeaders($this->getHeaders())
                    ->get($this->baseUrl.'/api2/products.json', [
                        'product_code' => $sku,
                        'limit' => 20,
                    ]);

                if ($response->successful()) {
                    foreach ($response->json('data') ?? [] as $entry) {
                        $product = $entry['Product'] ?? null;

                        if (! is_array($product) || empty($product['id'])) {
                            continue;
                        }

                        $productCode = trim((string) ($product['product_code'] ?? ''));

                        if ($productCode !== '' && strcasecmp($productCode, $sku) === 0) {
                            $result = [
                                'id' => (int) $product['id'],
                                'product_code' => $productCode,
                                'name' => trim((string) ($product['name'] ?? '')),
                            ];

                            Cache::put($cacheKey, $result, 3600);

                            return $result;
                        }
                    }
                }
            }

            $catalog = $this->getProductsCatalogIndex();
            $match = $this->matchProductInCatalog($catalog, $sku, $fallbackName);

            if ($match !== null) {
                $result = [
                    'id' => $match['id'],
                    'product_code' => $match['product_code'],
                    'name' => $match['name'],
                ];

                Cache::put($cacheKey, $result, 3600);

                return $result;
            }
        } catch (Exception $e) {
            Log::warning("Daftra product lookup failed for SKU [{$sku}]: ".$e->getMessage());
        }

        return null;
    }

    /**
     * Build and cache a lightweight index of Daftra products for fallback matching.
     */
    protected function getProductsCatalogIndex(): array
    {
        $cacheKey = 'daftra_products_catalog_index';
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        $catalog = [];
        $limit = 200;

        for ($page = 1; $page <= 20; $page++) {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl.'/api2/products.json', [
                    'page' => $page,
                    'limit' => $limit,
                ]);

            if (! $response->successful()) {
                break;
            }

            $json = $response->json();
            $items = $json['data'] ?? [];

            if (empty($items) || ! is_array($items)) {
                break;
            }

            foreach ($items as $entry) {
                $product = $entry['Product'] ?? null;

                if (! is_array($product) || empty($product['id'])) {
                    continue;
                }

                $catalog[] = [
                    'id' => (int) $product['id'],
                    'product_code' => trim((string) ($product['product_code'] ?? '')),
                    'barcode' => trim((string) ($product['barcode'] ?? '')),
                    'name' => trim((string) ($product['name'] ?? '')),
                ];
            }

            $pageCount = (int) ($json['pagination']['page_count'] ?? 1);

            if (count($items) < $limit || $page >= $pageCount) {
                break;
            }
        }

        if (! empty($catalog)) {
            Cache::put($cacheKey, $catalog, 1800);
        }

        return $catalog;
    }

    /**
     * Find the best matching catalog entry in strict priority order.
     */
    protected function matchProductInCatalog(array $catalog, string $sku, string $fallbackName): ?array
    {
        if ($sku !== '') {
            foreach ($catalog as $entry) {
                if ($entry['product_code'] !== '' && strcasecmp($entry['product_code'], $sku) === 0) {
                    return $entry;
                }
            }

            foreach ($catalog as $entry) {
                if ($entry['barcode'] !== '' && strcasecmp($entry['barcode'], $sku) === 0) {
                    return $entry;
                }
            }

            foreach ($catalog as $entry) {
                if ($entry['product_code'] !== '' && $this->productCodeMatchesSku($entry['product_code'], $sku)) {
                    return $entry;
                }
            }
        }

        if ($fallbackName !== '') {
            $normalizedFallback = mb_strtolower($fallbackName);
            $nameMatches = array_values(array_filter(
                $catalog,
                fn (array $entry): bool => $entry['name'] !== '' && mb_strtolower($entry['name']) === $normalizedFallback
            ));

            if (count($nameMatches) === 1) {
                return $nameMatches[0];
            }
        }

        return null;
    }

    /**
     * Match normalized or compound Daftra product_code values against a Bagisto SKU.
     */
    public function productCodeMatchesSku(string $productCode, string $sku): bool
    {
        $stripped = trim((string) preg_replace('/^sku\s*[-:]?\s*/i', '', $productCode));

        if ($stripped !== '' && strcasecmp($stripped, $sku) === 0) {
            return true;
        }

        $tokens = preg_split('/\s+-\s+|[,\/|]+/', $stripped) ?: [];

        foreach ($tokens as $token) {
            $cleaned = trim((string) preg_replace('/^sku\s*[-:]?\s*/i', '', trim($token)));

            if ($cleaned !== '' && strcasecmp($cleaned, $sku) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract normalized SKU candidates from a Daftra product_code and barcode in priority order.
     *
     * @return array<int, string>
     */
    public function extractSkuCandidates(?string $productCode, ?string $barcode = null): array
    {
        $candidates = [];
        $push = static function (string $value) use (&$candidates): void {
            $trimmed = trim($value);
            if ($trimmed !== '' && ! in_array($trimmed, $candidates, true)) {
                $candidates[] = $trimmed;
            }
        };

        $rawCode = trim((string) $productCode);
        if ($rawCode !== '') {
            $push($rawCode);

            $stripped = trim((string) preg_replace('/^sku\s*[-:]?\s*/i', '', $rawCode));
            $push($stripped);

            $tokens = preg_split('/\s+-\s+|[,\/|]+/', $stripped) ?: [];
            foreach ($tokens as $token) {
                $cleaned = trim((string) preg_replace('/^sku\s*[-:]?\s*/i', '', trim($token)));
                $push($cleaned);
            }
        }

        $rawBarcode = trim((string) $barcode);
        if ($rawBarcode !== '') {
            $push($rawBarcode);
        }

        return $candidates;
    }

    /**
     * Fetch live store balances for a batch of SKUs in parallel with short micro-caching.
     * Returns [$sku => float|null] where null indicates lookup failed/unavailable (fail-open).
     *
     * @param  array<int, array{sku: string, daftra_product_id?: int|null}>  $skuRequests
     * @return array<string, float|null>
     */
    public function getLiveStoreBalances(
        array $skuRequests,
        int $storeId,
        ?float $timeout = null,
        ?float $connectTimeout = null
    ): array {
        $timeout ??= (float) config('daftra.checkout_stock_timeout', 2.5);
        $connectTimeout ??= (float) config('daftra.checkout_stock_connect_timeout', 1.5);
        $cacheTtl = max(0, (int) config('daftra.checkout_stock_cache_ttl', 20));

        $results = [];
        $misses = [];

        foreach ($skuRequests as $req) {
            $sku = trim((string) ($req['sku'] ?? ''));
            if ($sku === '' || array_key_exists($sku, $results)) {
                continue;
            }

            $liveCacheKey = $this->liveStockCacheKey($sku, $storeId);
            if ($cacheTtl > 0 && Cache::has($liveCacheKey)) {
                $cachedQty = Cache::get($liveCacheKey);
                $results[$sku] = $cachedQty !== null ? (float) $cachedQty : null;

                continue;
            }

            $results[$sku] = null;

            $daftraId = ! empty($req['daftra_product_id'])
                ? (int) $req['daftra_product_id']
                : Cache::get($this->skuToDaftraIdCacheKey($sku));

            $misses[$sku] = [
                'sku' => $sku,
                'daftra_product_id' => $daftraId ? (int) $daftraId : null,
            ];
        }

        if (empty($misses)) {
            return $results;
        }

        try {
            $headers = $this->getHeaders($timeout, $connectTimeout);
        } catch (Exception $e) {
            Log::warning('Daftra live stock check skipped (auth unavailable): '.$e->getMessage());

            return $results;
        }

        $responses = Http::pool(function (Pool $pool) use ($misses, $headers, $timeout, $connectTimeout) {
            $calls = [];

            foreach ($misses as $sku => $req) {
                $query = $req['daftra_product_id']
                    ? ['id' => $req['daftra_product_id'], 'limit' => 1]
                    : ['product_code' => $sku, 'limit' => 5];

                $calls[] = $pool->as($sku)
                    ->withHeaders($headers)
                    ->connectTimeout($connectTimeout)
                    ->timeout($timeout)
                    ->get($this->baseUrl.'/api2/products.json', $query);
            }

            return $calls;
        });

        foreach ($misses as $sku => $req) {
            $response = $responses[$sku] ?? null;

            if (! $response instanceof Response || ! $response->successful()) {
                continue;
            }

            $items = $response->json('data');
            if (! is_array($items) || empty($items)) {
                continue;
            }

            $matchedProduct = null;

            foreach ($items as $entry) {
                $product = $entry['Product'] ?? null;
                if (! is_array($product) || empty($product['id'])) {
                    continue;
                }

                if ($req['daftra_product_id']) {
                    if ((int) $product['id'] === (int) $req['daftra_product_id']) {
                        $matchedProduct = $product;
                        break;
                    }

                    continue;
                }

                $productCode = (string) ($product['product_code'] ?? '');
                $barcode = trim((string) ($product['barcode'] ?? ''));

                if (
                    ($productCode !== '' && $this->productCodeMatchesSku($productCode, $sku))
                    || ($barcode !== '' && strcasecmp($barcode, $sku) === 0)
                ) {
                    $matchedProduct = $product;
                    break;
                }
            }

            if (! $matchedProduct) {
                continue;
            }

            Cache::put($this->skuToDaftraIdCacheKey($sku), (int) $matchedProduct['id'], 86400);

            $balance = $this->extractStoreBalanceFromProduct($matchedProduct, $storeId);
            $results[$sku] = $balance;

            if ($cacheTtl > 0) {
                Cache::put($this->liveStockCacheKey($sku, $storeId), $balance, $cacheTtl);
            }
        }

        return $results;
    }

    /**
     * Invalidate the short-lived checkout stock cache for the given SKUs.
     *
     * @param  array<int, string>  $skus
     */
    public function forgetLiveStockCache(array $skus, ?int $storeId = null): void
    {
        $storeId ??= (int) config('daftra.store_id', 2);

        foreach ($skus as $sku) {
            $sku = trim((string) $sku);
            if ($sku !== '') {
                Cache::forget($this->liveStockCacheKey($sku, $storeId));
            }
        }
    }

    /**
     * Cache key for a product's Daftra ID mapped from its Bagisto SKU.
     */
    public function skuToDaftraIdCacheKey(string $sku): string
    {
        return 'daftra_product_id_by_sku_'.md5(mb_strtolower(trim($sku)));
    }

    /**
     * Cache key for a SKU's short-lived live store balance.
     */
    protected function liveStockCacheKey(string $sku, int $storeId): string
    {
        return 'daftra_live_stock_'.$storeId.'_'.md5(mb_strtolower(trim($sku)));
    }

    /**
     * Extract a specific store's available balance from a Daftra Product payload.
     */
    public function extractStoreBalanceFromProduct(array $product, int $storeId): float
    {
        if ((int) ($product['deactivate'] ?? 0) === 1) {
            return 0.0;
        }

        $productStock = $product['ProductStock'] ?? null;

        if (! empty($productStock) && is_array($productStock)) {
            foreach ($productStock as $stockItem) {
                if ((int) ($stockItem['store_id'] ?? 0) === $storeId) {
                    return max(0.0, (float) ($stockItem['balance'] ?? 0));
                }
            }

            return 0.0;
        }

        $stockBalance = max(0.0, (float) ($product['stock_balance'] ?? 0));

        if ($stockBalance <= 0.0) {
            return 0.0;
        }

        return $storeId === (int) config('daftra.store_id', 2) ? $stockBalance : 0.0;
    }

    /**
     * احصل على منتج واحد
     */
    public function getProduct($productId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl."/api2/products/{$productId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في جلب المنتج: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في جلب المنتج: '.$e->getMessage());
        }
    }

    /**
     * إنشاء فاتورة جديدة
     */
    public function createInvoice($invoiceData)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->baseUrl.'/api2/invoices', $invoiceData);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في إنشاء الفاتورة: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في إنشاء الفاتورة: '.$e->getMessage());
        }
    }

    /**
     * تسجيل دفعة على فاتورة في دفترة
     */
    public function createInvoicePayment(array $paymentData)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->baseUrl.'/api2/invoice_payments', [
                    'InvoicePayment' => $paymentData,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في تسجيل دفعة الفاتورة: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في تسجيل دفعة الفاتورة: '.$e->getMessage());
        }
    }

    /**
     * حذف فاتورة في دفترة واسترجاع الكمية للمخزن تلقائياً
     */
    public function deleteInvoice(int $invoiceId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->delete($this->baseUrl."/api2/invoices/{$invoiceId}.json");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في حذف الفاتورة: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في حذف الفاتورة: '.$e->getMessage());
        }
    }

    /**
     * احصل على الفواتير
     */
    public function getInvoices($page = 1, $limit = 100)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl.'/api2/invoices', [
                    'page' => $page,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في جلب الفواتير: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في جلب الفواتير: '.$e->getMessage());
        }
    }

    /**
     * احصل على فاتورة واحدة
     */
    public function getInvoice($invoiceId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl."/api2/invoices/{$invoiceId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في جلب الفاتورة: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في جلب الفاتورة: '.$e->getMessage());
        }
    }

    /**
     * بناء بيانات الفاتورة
     */
    public function buildInvoiceData($invoiceDetails, $items = [], $payments = [], $currencyCode = 'KWD')
    {
        return [
            'Invoice' => array_merge([
                'staff_id' => 0,
                'subscription_id' => 26,
                'store_id' => (int) config('daftra.store_id', 2),
                'currency_code' => $currencyCode,
                'is_offline' => true,
                'draft' => false,
                'discount' => 0,
                'discount_amount' => 0,
                'deposit' => 0,
                'deposit_type' => 0,
                'invoice_layout_id' => (int) config('daftra.invoice_layout_id', 13),
                'estimate_id' => 0,
                'shipping_options' => 2,
                'shipping_amount' => null,
                'client_active_secondary_address' => false,
                'requisition_delivery_status' => 2,
            ], $invoiceDetails),
            'InvoiceItem' => $items,
            'Payment' => $payments,
            'InvoiceCustomField' => (object) [],
            'Deposit' => (object) [],
            'InvoiceReminder' => (object) [],
            'Document' => (object) [],
            'DocumentTitle' => (object) [],
        ];
    }

    /**
     * بناء عنصر فاتورة
     */
    public function buildInvoiceItem(
        $item,
        $description,
        $unitPrice,
        $quantity,
        $productId = null,
        $discount = 0,
        $storeId = null,
        $col3 = null
    ) {
        return [
            'item' => $item,
            'description' => $description,
            'unit_price' => (float) $unitPrice,
            'quantity' => (float) $quantity,
            'product_id' => $productId !== null ? (int) $productId : null,
            'discount' => (float) $discount,
            'discount_type' => 2,
            'tax1' => 0,
            'tax2' => 0,
            'store_id' => $storeId !== null ? (int) $storeId : (int) config('daftra.store_id', 2),
            'col_3' => $col3,
            'col_4' => null,
            'col_5' => null,
        ];
    }

    /**
     * بناء دفعة
     */
    public function buildPayment($paymentMethod, $amount, $date = null, $transactionId = 'payment', $treasuryId = 1, $status = null)
    {
        return [
            'payment_method' => $paymentMethod,
            'amount' => (float) $amount,
            'transaction_id' => $transactionId,
            'treasury_id' => (int) $treasuryId,
            'status' => $status !== null ? (int) $status : ($paymentMethod == 'cash' ? 0 : 1),
            'date' => $date ?? now()->format('Y-m-d H:i:s'),
            'staff_id' => 0,
        ];
    }

    /**
     * إنشاء عميل جديد
     */
    public function createClient($clientData)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->baseUrl.'/api2/clients', $clientData);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في إنشاء العميل: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في إنشاء العميل: '.$e->getMessage());
        }
    }

    /**
     * احصل على عميل واحد
     */
    public function getClient($clientId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl."/api2/clients/{$clientId}");

            if ($response->successful()) {
                return $response->json()['data']['Client'];
            }

            throw new Exception('فشل في جلب الفاتورة: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في جلب الفاتورة: '.$e->getMessage());
        }
    }

    /**
     * احصل على العملاء
     */
    public function getClients($page = 1, $limit = 100)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl.'/api2/clients', [
                    'page' => $page,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في جلب العملاء: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في جلب العملاء: '.$e->getMessage());
        }
    }

    /**
     * البحث عن عميل بواسطة البريد الإلكتروني
     */
    public function getClientsByEmail($email)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl.'/api2/clients.json', [
                    'email' => $email,
                    'limit' => 50,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * إنشاء مرتجع جديدة
     */
    public function createRefund($refundData)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->baseUrl.'/api2/refund_receipts', $refundData);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في إنشاء المرتجع: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في إنشاء المرتجع: '.$e->getMessage());
        }
    }

    /**
     * احصل على الفواتير
     */
    public function getRefunds($page = 1, $limit = 100)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl.'/api2/refund_receipts', [
                    'page' => $page,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في جلب الفواتير: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في جلب الفواتير: '.$e->getMessage());
        }
    }

    /**
     * احصل على مرتجع واحدة
     */
    public function getRefund($refundId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl."/api2/refund_receipts/{$refundId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('فشل في جلب المرتجع: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('خطأ في جلب المرتجع: '.$e->getMessage());
        }
    }

    /**
     * بناء بيانات المرتجع
     */
    public function buildRefundData($refundDetails, $items = [], $payments = [], $currencyCode = 'KWD')
    {
        return [
            'RefundReceipt' => array_merge([
                'staff_id' => 0,
                'subscription_id' => null,
                'store_id' => (int) config('daftra.store_id', 2),
                'currency_code' => $currencyCode,
                'is_offline' => true,
                'draft' => false,
                'discount' => 0,
                'discount_amount' => 0,
                'deposit' => 0,
                'deposit_type' => 0,
                'invoice_layout_id' => (int) config('daftra.invoice_layout_id', 13),
                'estimate_id' => 0,
                'shipping_options' => 2,
                'shipping_amount' => null,
                'client_active_secondary_address' => false,
                'requisition_delivery_status' => 2,
                'follow_up_status' => null,
                'work_order_id' => null,
                'pos_shift_id' => null,
            ], $refundDetails),
            'InvoiceItem' => $items,
            'InvoicePayment' => $payments,
            'InvoiceCustomField' => (object) [],
            'Deposit' => (object) [],
            'InvoiceReminder' => (object) [],
            'Document' => (object) [],
            'DocumentTitle' => (object) [],
        ];
    }
}
