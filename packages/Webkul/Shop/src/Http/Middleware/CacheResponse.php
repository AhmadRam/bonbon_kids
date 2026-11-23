<?php

namespace Webkul\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Middlewares\CacheResponse as BaseCacheResponseMiddleware;
use Spatie\ResponseCache\ResponseCache as BaseResponseCache;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Marketing\Repositories\SearchTermRepository;
use Webkul\Marketing\Repositories\URLRewriteRepository;
use Webkul\Product\Repositories\ProductRepository;

class CacheResponse extends BaseCacheResponseMiddleware
{
    /**
     * Create a middleware instance.
     *
     * @return void
     */
    public function __construct(protected BaseResponseCache $responseCache)
    {
        $this->responseCache = $responseCache;

        parent::__construct($responseCache);
    }

    /**
     * Handle an incoming request.
     *
     * @param  mixed  ...$args
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$args): Response
    {
        if (! $this->responseCache->enabled($request)) {
            return parent::handle($request, $next, ...$args);
        }

        /**
         * Redirect to the search term redirect url if the search term is found.
         */
        if ($request->route()->getName() == 'shop.search.index') {
            if ($request->query('suggest') === '0') {
                return $next($request);
            }

            $searchTerm = app(SearchTermRepository::class)->findOneWhere([
                'term' => request()->query('query'),
                'channel_id' => core()->getCurrentChannel()->id,
                'locale' => app()->getLocale(),
            ]);

            if ($searchTerm?->redirect_url) {
                return redirect()->to($searchTerm->redirect_url);
            }
        }

        /**
         * Redirect to the target path if a safe url rewrite is found.
         */
        if ($request->route()->getName() == 'shop.product_or_category.index') {
            $slugOrPath = rawurldecode(trim($request->getPathInfo(), '/'));

            if ($redirect = $this->resolveSafeRewriteRedirect('category', $slugOrPath)) {
                return $redirect;
            }

            if ($redirect = $this->resolveSafeRewriteRedirect('product', $slugOrPath)) {
                return $redirect;
            }
        }

        /**
         * Redirect to the target path if the cms page url rewrite is found.
         */
        if ($request->route()->getName() == 'shop.cms.page') {
            $slug = last(explode('/', $request->getPathInfo()));

            $pageURLRewrite = app(URLRewriteRepository::class)->findOneWhere([
                'entity_type' => 'cms_page',
                'request_path' => $slug,
                'locale' => app()->getLocale(),
            ]);

            if ($pageURLRewrite) {
                return redirect()->to($pageURLRewrite->target_path, $pageURLRewrite->redirect_type);
            }
        }

        return parent::handle($request, $next, ...$args);
    }

    /**
     * Resolve a URL rewrite for a category or product only when it does not hijack
     * a live entity and points to an active, non-looping target.
     */
    protected function resolveSafeRewriteRedirect(string $entityType, string $slugOrPath): ?Response
    {
        $urlRewrite = app(URLRewriteRepository::class)->findOneWhere([
            'entity_type' => $entityType,
            'request_path' => $slugOrPath,
            'locale' => app()->getLocale(),
        ]);

        if (! $urlRewrite) {
            return null;
        }

        $rawTarget = trim((string) $urlRewrite->target_path);
        $normalizedTarget = trim($rawTarget, '/');

        if ($normalizedTarget === '' || $normalizedTarget === $slugOrPath) {
            return null;
        }

        if ($this->isEntitySlugActive('category', $slugOrPath)) {
            return null;
        }

        if ($entityType === 'product' && $this->isEntitySlugActive('product', $slugOrPath)) {
            return null;
        }

        if (preg_match('#^https?://#i', $rawTarget)) {
            return redirect()->to($rawTarget, (int) ($urlRewrite->redirect_type ?: 301));
        }

        if (! $this->isEntitySlugActive($entityType, $normalizedTarget)) {
            return null;
        }

        return redirect()->to($urlRewrite->target_path, (int) ($urlRewrite->redirect_type ?: 301));
    }

    /**
     * Determine whether an active storefront entity owns the given slug.
     */
    protected function isEntitySlugActive(string $entityType, string $slug): bool
    {
        if ($entityType === 'category') {
            $category = app(CategoryRepository::class)->findBySlug($slug);

            return (bool) ($category && $category->status);
        }

        if ($entityType === 'product') {
            $product = app(ProductRepository::class)->findBySlug($slug);

            return (bool) ($product && $product->status && $product->visible_individually);
        }

        return false;
    }
}
