<?php

namespace Webkul\Marketing\Listeners;

use Illuminate\Support\Facades\Event;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Marketing\Repositories\URLRewriteRepository;

class Category
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
        protected CategoryRepository $categoryRepository,
        protected URLRewriteRepository $urlRewriteRepository
    ) {}

    /**
     * After category is created
     *
     * @param  \Webkul\Category\Contracts\Category  $category
     * @return void
     */
    public function afterCreate($category)
    {
        if (! $category || empty($category->slug)) {
            return;
        }

        /**
         * Delete category and product url rewrites
         * if already exists for the request path
         */
        $urlRewrites = $this->urlRewriteRepository->findWhere([
            ['entity_type', 'IN', ['category', 'product']],
            'request_path' => $category->slug,
        ]);

        $this->deleteUrlRewrites($urlRewrites);
    }

    /**
     * Before category is updated
     *
     * @param  int  $id
     * @return void
     */
    public function beforeUpdate($id)
    {
        $locale = request()->input('locale');

        $category = $this->categoryRepository->find($id);

        if (! $category || ! $locale) {
            return;
        }

        $currentURLKey = trim((string) request()->input($locale.'.slug'));

        if ($currentURLKey === '') {
            return;
        }

        /**
         * Delete category and product url rewrites claiming the new slug.
         */
        $conflictingRewrites = $this->urlRewriteRepository->findWhere([
            ['entity_type', 'IN', ['category', 'product']],
            'request_path' => $currentURLKey,
        ]);

        $this->deleteUrlRewrites($conflictingRewrites);

        $translations = $category->translate($locale);

        /**
         * If url key is empty for requested locale or unchanged then return
         */
        if (
            empty($translations['slug'])
            || $translations['slug'] === $currentURLKey
        ) {
            return;
        }

        /**
         * Delete category and product url rewrites
         * targeting the old slug in this locale, and any duplicate for old request_path.
         */
        $targetRewrites = $this->urlRewriteRepository->findWhere([
            ['entity_type', 'IN', ['category', 'product']],
            'target_path' => $translations['slug'],
            'locale' => $locale,
        ]);

        $this->deleteUrlRewrites($targetRewrites);

        $existingRewrites = $this->urlRewriteRepository->findWhere([
            'entity_type' => 'category',
            'request_path' => $translations['slug'],
            'locale' => $locale,
        ]);

        $this->deleteUrlRewrites($existingRewrites);

        $incomingStatus = request()->has('status')
            ? (bool) request()->input('status')
            : (bool) $category->status;

        if (! $incomingStatus) {
            return;
        }

        Event::dispatch('marketing.search_seo.url_rewrites.create.before');

        $urlRewrite = $this->urlRewriteRepository->create([
            'entity_type' => 'category',
            'request_path' => $translations['slug'],
            'target_path' => $currentURLKey,
            'locale' => $locale,
            'redirect_type' => self::PERMANENT_REDIRECT_CODE,
        ]);

        Event::dispatch('marketing.search_seo.url_rewrites.create.after', $urlRewrite);
    }

    /**
     * Before category is deleted
     *
     * @param  int  $id
     * @return void
     */
    public function beforeDelete($id)
    {
        $category = $this->categoryRepository->find($id);

        if (! $category) {
            return;
        }

        /**
         * Delete all url rewrites for all locales
         */
        $translations = $category->getTranslationsArray();

        foreach ($translations as $locale => $translation) {
            if (empty($translation['slug'])) {
                continue;
            }

            $requestRewrites = $this->urlRewriteRepository->findWhere([
                'entity_type' => 'category',
                'request_path' => $translation['slug'],
                'locale' => $locale,
            ]);

            $this->deleteUrlRewrites($requestRewrites);

            $targetRewrites = $this->urlRewriteRepository->findWhere([
                'entity_type' => 'category',
                'target_path' => $translation['slug'],
                'locale' => $locale,
            ]);

            $this->deleteUrlRewrites($targetRewrites);
        }
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
