<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\Request;
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

            return view('shop::home.index', compact('customizations'));
        }

        /**
         * Support url for chinese, japanese, arabic and english with numbers.
         */
        if (! preg_match('/^([\p{L}\p{N}\p{M}\x{0900}-\x{097F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0400}-\x{04FF}_ \+\-]+\/?)+$/u', $slugOrURLKey)) {
            abort(404);
        }

        $category = $this->categoryRepository->findBySlug($slugOrURLKey);

        if ($category) {
            $searchEngine = 'database';

            if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
                $searchEngine = core()->getConfigData('catalog.products.search.storefront_mode');
            }

            $products = $this->productRepository
                ->setSearchEngine($searchEngine)
                ->getAll(array_merge(
                    request()->query(),
                    [
                        'channel_id'           => core()->getCurrentChannel()->id,
                        'status'               => 1,
                        'visible_individually' => 1,
                        'category_id'          => $category->id,
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
                ! $product->url_key
                || ! $product->visible_individually
                || ! $product->status
            ) {
                abort(404);
            }

            $productURLRewrite = $this->urlRewriteRepository->findOneWhere([
                'entity_type' => 'product',
                'request_path' => $slugOrURLKey,
                'locale' => app()->getLocale(),
            ]);

            if ($productURLRewrite) {
                return redirect()->to($productURLRewrite->target_path, $productURLRewrite->redirect_type);
            }

            return view('shop::products.view', compact('product'));
        }

        /**
         * Cross-locale slug fallback:
         */
        $crossLocaleProductAttribute = \Illuminate\Support\Facades\DB::table('product_attribute_values as pav_url')
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
            if ($product && $product->url_key && $product->url_key !== $slugOrURLKey) {
                return redirect()->to($product->url_key, 301);
            } elseif ($product && $product->url_key === $slugOrURLKey) {
                if (
                    $product->visible_individually
                    && $product->status
                ) {
                    return view('shop::products.view', compact('product'));
                }
            }
        }

        if (str_contains($slugOrURLKey, '/')) {
            $trimmedSlug = last(explode('/', $slugOrURLKey));
            $category = $this->categoryRepository->findBySlug($trimmedSlug);
            if ($category) {
                return redirect()->to($trimmedSlug, 301);
            }
        }

        $urlRewrite = $this->urlRewriteRepository->findOneWhere([
            'request_path' => $slugOrURLKey,
            'locale' => app()->getLocale(),
        ]);

        if ($urlRewrite) {
            return redirect()->to($urlRewrite->target_path, $urlRewrite->redirect_type);
        }

        abort(404);
    }
}
