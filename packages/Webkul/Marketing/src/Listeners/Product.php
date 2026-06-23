<?php

namespace Webkul\Marketing\Listeners;

use Illuminate\Support\Facades\Event;
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
     * After product is updated
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

        if (is_array($currentURLKey)) {
            foreach ($currentURLKey as $localeCode => $newURLKey) {
                if (! $newURLKey) {
                    continue;
                }

                $oldURLKey = $this->getOldURLKey($product, $localeCode);

                if ($newURLKey === $oldURLKey) {
                    continue;
                }

                $this->handleURLRewrite($product, $oldURLKey, $newURLKey, $localeCode);
            }
        } else {
            if ($currentURLKey === $product->url_key) {
                return;
            }

            $this->handleURLRewrite($product, $product->url_key, $currentURLKey, core()->getRequestedLocaleCode());
        }
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
        $attribute = app(\Webkul\Attribute\Repositories\AttributeRepository::class)->findOneByField('code', 'url_key');
        
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
        if (empty($oldURLKey)) {
            /**
             * Delete category and product url rewrites
             * if already exists for the request path
             */
            $urlRewrites = $this->urlRewriteRepository->findWhere([
                ['entity_type', 'IN', ['category', 'product']],
                'request_path' => $newURLKey,
            ]);

            foreach ($urlRewrites as $urlRewrite) {
                Event::dispatch('marketing.search_seo.url_rewrites.delete.before', $urlRewrite->id);

                $this->urlRewriteRepository->delete($urlRewrite->id);

                Event::dispatch('marketing.search_seo.url_rewrites.delete.after', $urlRewrite->id);
            }

            return;
        }

        /**
         * Delete category and product url rewrites
         * if already exists for the request path
         */
        $urlRewrites = $this->urlRewriteRepository->findWhere([
            ['entity_type', 'IN', ['category', 'product']],
            'target_path' => $oldURLKey,
        ]);

        foreach ($urlRewrites as $urlRewrite) {
            Event::dispatch('marketing.search_seo.url_rewrites.delete.before', $urlRewrite->id);

            $this->urlRewriteRepository->delete($urlRewrite->id);

            Event::dispatch('marketing.search_seo.url_rewrites.delete.after', $urlRewrite->id);
        }

        Event::dispatch('marketing.search_seo.url_rewrites.create.before');

        $urlRewrite = $this->urlRewriteRepository->create([
            'entity_type' => 'product',
            'request_path' => $oldURLKey,
            'target_path' => $newURLKey ?? '',
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

        /**
         * Delete product url rewrites
         * if already exists for the request path
         */
        $urlRewrites = $this->urlRewriteRepository->findWhere([
            'entity_type' => 'product',
            'request_path' => $product->url_key,
        ]);

        foreach ($urlRewrites as $urlRewrite) {
            Event::dispatch('marketing.search_seo.url_rewrites.delete.before', $urlRewrite->id);

            $this->urlRewriteRepository->delete($urlRewrite->id);

            Event::dispatch('marketing.search_seo.url_rewrites.delete.after', $urlRewrite->id);
        }
    }
}
