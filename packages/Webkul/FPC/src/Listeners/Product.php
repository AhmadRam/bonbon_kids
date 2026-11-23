<?php

namespace Webkul\FPC\Listeners;

use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Product\Repositories\ProductBundleOptionProductRepository;
use Webkul\Product\Repositories\ProductGroupedProductRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Controllers\SitemapController;

class Product
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductBundleOptionProductRepository $productBundleOptionProductRepository,
        protected ProductGroupedProductRepository $productGroupedProductRepository
    ) {}

    /**
     * Update or create product page cache
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function afterUpdate($product)
    {
        if (! $product) {
            return;
        }

        $urls = $this->getForgettableUrls($product);

        if (! empty($urls)) {
            ResponseCache::forget($urls);
        }

        if (class_exists(SitemapController::class)) {
            SitemapController::clearCache();
        }
    }

    /**
     * Delete product page cache
     *
     * @param  int  $productId
     * @return void
     */
    public function beforeDelete($productId)
    {
        $product = $this->productRepository->find($productId);

        if (! $product) {
            return;
        }

        $urls = $this->getForgettableUrls($product);

        if (! empty($urls)) {
            ResponseCache::forget($urls);
        }

        if (class_exists(SitemapController::class)) {
            SitemapController::clearCache();
        }
    }

    /**
     * Returns product urls across all locales
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return array
     */
    public function getForgettableUrls($product)
    {
        $urls = [];

        $products = $this->getAllRelatedProducts($product);
        $productIds = collect($products)->pluck('id')->filter()->unique()->values()->all();

        if (! empty($productIds)) {
            $urlKeys = DB::table('product_attribute_values')
                ->join('attributes', 'attributes.id', '=', 'product_attribute_values.attribute_id')
                ->where('attributes.code', 'url_key')
                ->whereIn('product_attribute_values.product_id', $productIds)
                ->whereNotNull('product_attribute_values.text_value')
                ->where('product_attribute_values.text_value', '!=', '')
                ->pluck('product_attribute_values.text_value')
                ->all();

            foreach ($urlKeys as $urlKey) {
                $urls[] = '/'.ltrim((string) $urlKey, '/');
            }
        }

        foreach ($products as $relatedProduct) {
            if (! empty($relatedProduct->url_key)) {
                $urls[] = '/'.ltrim((string) $relatedProduct->url_key, '/');
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Returns parents bundle products associated with simple product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return array
     */
    public function getAllRelatedProducts($product)
    {
        $products = [$product];

        if ($product->type == 'simple') {
            if ($product->parent_id) {
                $products[] = $product->parent;
            }

            $products = array_merge(
                $products,
                $this->getParentBundleProducts($product),
                $this->getParentGroupProducts($product)
            );
        } elseif ($product->type == 'configurable') {
            $products = [];

            /**
             * Fetching fresh variants.
             */
            foreach ($product->variants()->get() as $variant) {
                $products[] = $variant;
            }

            $products[] = $product;
        }

        return $products;
    }

    /**
     * Returns parents bundle products associated with simple product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return array
     */
    public function getParentBundleProducts($product)
    {
        $bundleOptionProducts = $this->productBundleOptionProductRepository->findWhere([
            'product_id' => $product->id,
        ]);

        $products = [];

        foreach ($bundleOptionProducts as $bundleOptionProduct) {
            $products[] = $bundleOptionProduct->bundle_option->product;
        }

        return $products;
    }

    /**
     * Returns parents group products associated with simple product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return array
     */
    public function getParentGroupProducts($product)
    {
        $groupedOptionProducts = $this->productGroupedProductRepository->findWhere([
            'associated_product_id' => $product->id,
        ]);

        $products = [];

        foreach ($groupedOptionProducts as $groupedOptionProduct) {
            $products[] = $groupedOptionProduct->product;
        }

        return $products;
    }
}
