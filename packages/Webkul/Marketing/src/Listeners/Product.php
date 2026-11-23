<?php

namespace Webkul\Marketing\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Marketing\Repositories\URLRewriteRepository;
use Webkul\Product\Repositories\ProductRepository;

class Product
{
    /**
     * Permanent redirect code
     *
     * @var int
     */
    const PERMANENT_REDIRECT_CODE = 301;

    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected URLRewriteRepository $urlRewriteRepository
    ) {}

    /**
     * After product is created, remove any stale rewrites that claim its URL keys.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     */
    public function afterCreate($product): void
    {
        if (! $product) {
            return;
        }

        $urlKeys = $this->getAllProductUrlKeys($product);

        if (empty($urlKeys)) {
            return;
        }

        $urlRewrites = $this->urlRewriteRepository->findWhere([
            ['entity_type', 'IN', ['category', 'product']],
            ['request_path', 'IN', $urlKeys],
        ]);

        $this->deleteUrlRewrites($urlRewrites);
    }

    /**
     * Before product is updated
     *
     * @param  int  $id
     * @return void
     */
    public function beforeUpdate($id)
    {
        $currentURLKey = request()->input('url_key');

        if (! $currentURLKey) {
            return;
        }

        $product = $this->productRepository->find($id);

        if (! $product) {
            return;
        }

        if (is_array($currentURLKey)) {
            foreach ($currentURLKey as $localeCode => $newURLKey) {
                $newURLKey = trim((string) $newURLKey);

                if ($newURLKey === '') {
                    continue;
                }

                $oldURLKey = $this->getOldURLKey($product, $localeCode);

                if ($newURLKey === $oldURLKey) {
                    continue;
                }

                $this->handleURLRewrite($product, $oldURLKey, $newURLKey, $localeCode);
            }
        } else {
            $currentURLKey = trim((string) $currentURLKey);

            if ($currentURLKey === '') {
                return;
            }

            $localeCode = request()->input('locale') ?: core()->getRequestedLocaleCode();
            $oldURLKey = $this->getOldURLKey($product, $localeCode);

            if ($currentURLKey === $oldURLKey) {
                return;
            }

            $this->handleURLRewrite($product, $oldURLKey, $currentURLKey, $localeCode);
        }
    }

    /**
     * After product is updated, ensure rewrites reflect its live status and URL keys.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     */
    public function afterUpdate($product): void
    {
        if (! $product) {
            return;
        }

        $freshProduct = $this->productRepository->find($product->id);

        if (! $freshProduct) {
            return;
        }

        $urlKeys = $this->getAllProductUrlKeys($freshProduct);

        if (empty($urlKeys)) {
            return;
        }

        if (! $freshProduct->status || ! $freshProduct->visible_individually) {
            $deadTargetRewrites = $this->urlRewriteRepository->findWhere([
                'entity_type' => 'product',
                ['target_path', 'IN', $urlKeys],
            ]);

            $this->deleteUrlRewrites($deadTargetRewrites);

            return;
        }

        $conflictingRewrites = $this->urlRewriteRepository->findWhere([
            ['entity_type', 'IN', ['category', 'product']],
            ['request_path', 'IN', $urlKeys],
        ]);

        $this->deleteUrlRewrites($conflictingRewrites);
    }

    /**
     * Retrieve the old URL Key for a specific locale
     *
     * @param  mixed  $product
     * @param  string  $localeCode
     * @return string|null
     */
    protected function getOldURLKey($product, $localeCode)
    {
        $attribute = app(AttributeRepository::class)->findOneByField('code', 'url_key');

        if (! $attribute) {
            return null;
        }

        $channel = $attribute->value_per_channel ? core()->getRequestedChannelCode() : null;

        $attrValModel = $product->attribute_values
            ->where('attribute_id', $attribute->id)
            ->where('channel', $channel)
            ->where('locale', $localeCode)
            ->first();

        return $attrValModel ? $attrValModel[$attribute->column_name] : null;
    }

    /**
     * Handle URL Rewrite creation/deletion logic
     *
     * @param  mixed  $product
     * @param  string|null  $oldURLKey
     * @param  string  $newURLKey
     * @param  string  $localeCode
     * @return void
     */
    protected function handleURLRewrite($product, $oldURLKey, $newURLKey, $localeCode)
    {
        /**
         * Delete category and product url rewrites
         * if already exists for the new request path.
         */
        $conflictingRewrites = $this->urlRewriteRepository->findWhere([
            ['entity_type', 'IN', ['category', 'product']],
            'request_path' => $newURLKey,
        ]);

        $this->deleteUrlRewrites($conflictingRewrites);

        if (empty($oldURLKey) || $oldURLKey === $newURLKey) {
            return;
        }

        /**
         * Delete category and product url rewrites
         * targeting the old path in this locale, and any duplicate for old request_path.
         */
        $targetRewrites = $this->urlRewriteRepository->findWhere([
            ['entity_type', 'IN', ['category', 'product']],
            'target_path' => $oldURLKey,
            'locale' => $localeCode,
        ]);

        $this->deleteUrlRewrites($targetRewrites);

        $existingRewrites = $this->urlRewriteRepository->findWhere([
            'entity_type' => 'product',
            'request_path' => $oldURLKey,
            'locale' => $localeCode,
        ]);

        $this->deleteUrlRewrites($existingRewrites);

        $incomingStatus = request()->has('status')
            ? (bool) request()->input('status')
            : (bool) $product->status;

        if (! $incomingStatus) {
            return;
        }

        Event::dispatch('marketing.search_seo.url_rewrites.create.before');

        $urlRewrite = $this->urlRewriteRepository->create([
            'entity_type' => 'product',
            'request_path' => $oldURLKey,
            'target_path' => $newURLKey,
            'locale' => $localeCode,
            'redirect_type' => self::PERMANENT_REDIRECT_CODE,
        ]);

        Event::dispatch('marketing.search_seo.url_rewrites.create.after', $urlRewrite);
    }

    /**
     * Before product is deleted
     *
     * @param  int  $id
     * @return void
     */
    public function beforeDelete($id)
    {
        $product = $this->productRepository->find($id);

        if (! $product) {
            return;
        }

        $urlKeys = $this->getAllProductUrlKeys($product);

        if (empty($urlKeys)) {
            return;
        }

        $requestRewrites = $this->urlRewriteRepository->findWhere([
            'entity_type' => 'product',
            ['request_path', 'IN', $urlKeys],
        ]);

        $this->deleteUrlRewrites($requestRewrites);

        $targetRewrites = $this->urlRewriteRepository->findWhere([
            'entity_type' => 'product',
            ['target_path', 'IN', $urlKeys],
        ]);

        $this->deleteUrlRewrites($targetRewrites);
    }

    /**
     * Retrieve all non-empty url_key values for the product across all locales and channels.
     */
    protected function getAllProductUrlKeys($product): array
    {
        $attribute = app(AttributeRepository::class)->findOneByField('code', 'url_key');

        if (! $attribute) {
            return [];
        }

        return DB::table('product_attribute_values')
            ->where('product_id', $product->id)
            ->where('attribute_id', $attribute->id)
            ->whereNotNull('text_value')
            ->where('text_value', '!=', '')
            ->pluck('text_value')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Delete a collection of URL rewrites while firing expected lifecycle events.
     */
    protected function deleteUrlRewrites($urlRewrites): void
    {
        foreach ($urlRewrites as $urlRewrite) {
            Event::dispatch('marketing.search_seo.url_rewrites.delete.before', $urlRewrite->id);

            $this->urlRewriteRepository->delete($urlRewrite->id);

            Event::dispatch('marketing.search_seo.url_rewrites.delete.after', $urlRewrite->id);
        }
    }
}
