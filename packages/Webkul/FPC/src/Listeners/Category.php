<?php

namespace Webkul\FPC\Listeners;

use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Shop\Http\Controllers\SitemapController;

class Category
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected CategoryRepository $categoryRepository) {}

    /**
     * After category update
     *
     * @param  \Webkul\Category\Contracts\Category  $category
     * @return void
     */
    public function afterUpdate($category)
    {
        if (! $category) {
            return;
        }

        $this->forgetCategoryCache($category);
    }

    /**
     * Before category delete
     *
     * @param  int  $categoryId
     * @return void
     */
    public function beforeDelete($categoryId)
    {
        $category = $this->categoryRepository->find($categoryId);

        if (! $category) {
            return;
        }

        $this->forgetCategoryCache($category);
    }

    /**
     * Forget response cache for all category slugs and clear sitemap cache.
     */
    protected function forgetCategoryCache($category): void
    {
        foreach (core()->getAllLocales() as $locale) {
            if ($categoryTranslation = $category->translate($locale->code)) {
                if (! empty($categoryTranslation->slug)) {
                    ResponseCache::forget($categoryTranslation->slug);
                }
            }
        }

        $defaultSlug = $category->translate(core()->getDefaultLocaleCodeFromDefaultChannel())?->slug;

        if (! empty($defaultSlug)) {
            ResponseCache::forget($defaultSlug);
        }

        if (class_exists(SitemapController::class)) {
            SitemapController::clearCache();
        }
    }
}
