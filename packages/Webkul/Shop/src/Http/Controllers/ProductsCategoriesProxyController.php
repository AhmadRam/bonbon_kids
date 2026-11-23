<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Marketing\Repositories\URLRewriteRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class ProductsCategoriesProxyController extends Controller
{
    /**
     * Using const variable for status
     *
     * @var int Status
     */
    const STATUS = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected ThemeCustomizationRepository $themeCustomizationRepository,
        protected URLRewriteRepository $urlRewriteRepository
    ) {}

    /**
     * Show product or category view. If neither category nor product matches, abort with code 404.
     *
     * @return View|\Exception
     */
    public function index(Request $request)
    {
        $slugOrURLKey = rawurldecode(trim($request->getPathInfo(), '/'));

        if (empty($slugOrURLKey)) {
            $customizations = $this->themeCustomizationRepository->orderBy('sort_order')->findWhere([
                'status' => self::STATUS,
                'channel_id' => core()->getCurrentChannel()->id,
            ]);

            $carouselExcludeIds = app(HomeController::class)->resolveCarouselExcludeIds($customizations);

            return view('shop::home.index', compact('customizations', 'carouselExcludeIds'));
        }

        /**
         * Support url for chinese, japanese, arabic and english with numbers.
         */
        if (! preg_match('/^([\p{L}\p{N}\p{M}\x{0900}-\x{097F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0400}-\x{04FF}_ \+\-]+\/?)+$/u', $slugOrURLKey)) {
            abort(404);
        }

        $category = $this->categoryRepository->findBySlug($slugOrURLKey);

        if ($category) {
            if (! $category->status) {
                abort(404);
            }

            $searchEngine = 'database';

            if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
                $searchEngine = core()->getConfigData('catalog.products.search.storefront_mode');
            }

            $products = $this->productRepository
                ->setSearchEngine($searchEngine)
                ->getAll(array_merge(
                    request()->query(),
                    [
                        'channel_id' => core()->getCurrentChannel()->id,
                        'status' => 1,
                        'visible_individually' => 1,
                        'category_id' => $category->id,
                    ]
                ));

            return view('shop::categories.view', [
                'category' => $category,
                'products' => $products,
                'params' => [
                    'sort' => request()->query('sort'),
                    'limit' => request()->query('limit'),
                    'mode' => request()->query('mode'),
                ],
            ]);
        }

        if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
            $searchEngine = core()->getConfigData('catalog.products.search.storefront_mode');
        }

        $product = $this->productRepository
            ->setSearchEngine($searchEngine ?? 'database')
            ->findBySlug($slugOrURLKey);

        if ($product) {
            if (
                $product->url_key
                && $product->visible_individually
                && $product->status
            ) {
                return view('shop::products.view', compact('product'));
            }

            if ($redirect = $this->resolveSafeUrlRewrite($slugOrURLKey)) {
                return $redirect;
            }

            abort(404);
        }

        /**
         * Cross-locale slug fallback:
         */
        $crossLocaleProductAttribute = DB::table('product_attribute_values as pav_url')
            ->join('attributes as a_url', 'pav_url.attribute_id', '=', 'a_url.id')
            ->leftJoin('product_attribute_values as pav_status', function ($join) {
                $join->on('pav_url.product_id', '=', 'pav_status.product_id')
                    ->whereIn('pav_status.attribute_id', function ($query) {
                        $query->select('id')->from('attributes')->where('code', 'status');
                    });
            })
            ->where('a_url.code', 'url_key')
            ->where('pav_url.text_value', $slugOrURLKey)
            ->orderByDesc('pav_status.boolean_value')
            ->select('pav_url.product_id')
            ->first();

        if ($crossLocaleProductAttribute) {
            $product = $this->productRepository->find($crossLocaleProductAttribute->product_id);

            if (
                $product
                && $product->status
                && $product->visible_individually
                && $product->url_key
            ) {
                if ($product->url_key !== $slugOrURLKey) {
                    return redirect()->to($product->url_key, 301);
                }

                return view('shop::products.view', compact('product'));
            }
        }

        if (str_contains($slugOrURLKey, '/')) {
            $trimmedSlug = last(explode('/', $slugOrURLKey));
            $category = $this->categoryRepository->findBySlug($trimmedSlug);
            if ($category && $category->status) {
                return redirect()->to($trimmedSlug, 301);
            }
        }

        if ($redirect = $this->resolveSafeUrlRewrite($slugOrURLKey)) {
            return $redirect;
        }

        abort(404);
    }

    /**
     * Resolve a URL rewrite only if it is non-looping and points to a live target.
     */
    protected function resolveSafeUrlRewrite(string $slugOrURLKey)
    {
        $urlRewrite = $this->urlRewriteRepository->findOneWhere([
            'request_path' => $slugOrURLKey,
            'locale' => app()->getLocale(),
        ]);

        if (! $urlRewrite) {
            return null;
        }

        $targetPath = trim((string) $urlRewrite->target_path);
        $normalizedTarget = trim($targetPath, '/');

        if ($normalizedTarget === '' || $normalizedTarget === $slugOrURLKey) {
            return null;
        }

        if (preg_match('#^https?://#i', $targetPath)) {
            return redirect()->to($targetPath, (int) ($urlRewrite->redirect_type ?: 301));
        }

        if ($urlRewrite->entity_type === 'product') {
            $targetProduct = $this->productRepository->findBySlug($normalizedTarget);

            if (! $targetProduct || ! $targetProduct->status || ! $targetProduct->visible_individually) {
                return null;
            }
        } elseif ($urlRewrite->entity_type === 'category') {
            $targetCategory = $this->categoryRepository->findBySlug($normalizedTarget);

            if (! $targetCategory || ! $targetCategory->status) {
                return null;
            }
        }

        return redirect()->to($urlRewrite->target_path, (int) ($urlRewrite->redirect_type ?: 301));
    }
}
