<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Marketing\Jobs\UpdateCreateSearchTerm as UpdateCreateSearchTermJob;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Resources\ProductResource;

class ProductController extends APIController
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Product listings.
     */
    public function index(): JsonResource
    {
        $searchEngine = 'database';

        if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
            $searchEngine = core()->getConfigData('catalog.products.search.storefront_mode');
        }

        $searchData = $this->resolveSearchQueryData($searchEngine);

        $query = $searchData['effective_query'] ?? $searchData['original_query'];

        $params = request()->query();
        if (isset($params['group'])) {
            $groupValues = explode(',', $params['group']);
            $mappedIds = [];
            foreach ($groupValues as $value) {
                if (is_numeric($value)) {
                    $mappedIds[] = $value;
                    continue;
                }

                $option = \Illuminate\Support\Facades\DB::table('attribute_options')
                    ->join('attribute_option_translations', 'attribute_options.id', '=', 'attribute_option_translations.attribute_option_id')
                    ->whereIn('attribute_options.attribute_id', function ($q) {
                        $q->select('id')->from('attributes')->where('code', 'group');
                    })
                    ->where(function ($q) use ($value) {
                        $q->where('attribute_option_translations.label', $value)
                          ->orWhere('attribute_options.admin_name', $value)
                          ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(attribute_option_translations.label)'), strtolower($value))
                          ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(attribute_options.admin_name)'), strtolower($value))
                          ->orWhere(\Illuminate\Support\Facades\DB::raw("REPLACE(LOWER(attribute_options.admin_name), ' ', '-')"), strtolower($value))
                          ->orWhere(\Illuminate\Support\Facades\DB::raw("REPLACE(LOWER(attribute_option_translations.label), ' ', '-')"), strtolower($value));
                    })
                    ->first();

                if ($option) {
                    $mappedIds[] = $option->attribute_option_id;
                }
            }

            if (! empty($mappedIds)) {
                $params['group'] = implode(',', $mappedIds);
            }
        }

        $products = $this->productRepository
            ->setSearchEngine($searchEngine)
            ->getAll(array_merge($params, [
                'query' => $query,
                'channel_id' => core()->getCurrentChannel()->id,
                'status' => 1,
                'visible_individually' => 1,
            ]));

        if (! empty($query)) {
            /**
             * Update or create search term only if
             * there is only one filter that is query param
             */
            if (count(request()->except(['mode', 'sort', 'limit'])) == 1) {
                UpdateCreateSearchTermJob::dispatch([
                    'term' => $query,
                    'results' => $products->total(),
                    'channel_id' => core()->getCurrentChannel()->id,
                    'locale' => app()->getLocale(),
                ]);
            }
        }

        return ProductResource::collection($products);
    }

    /**
     * Resolve search query data.
     */
    protected function resolveSearchQueryData($searchEngine): array
    {
        if (request()->query('suggest', '') === '0') {
            return [
                'original_query' => request()->query('query', ''),
                'effective_query' => null,
            ];
        }

        $originalQuery = request()->query('query', '');

        return [
            'original_query' => $originalQuery,
            'effective_query' => $this->getEffectiveQuery($originalQuery, $searchEngine),
        ];
    }

    /**
     * It will return the effective query based on the search engine.
     */
    protected function getEffectiveQuery(string $originalQuery, string $searchEngine): ?string
    {
        $effectiveQuery = $this->productRepository->setSearchEngine($searchEngine)->getSuggestions($originalQuery);

        return $effectiveQuery;
    }

    /**
     * Related product listings.
     *
     * @param  int  $id
     */
    public function relatedProducts($id): JsonResource
    {
        $product = $this->productRepository->findOrFail($id);

        $relatedProducts = $product->related_products()
            ->take(core()->getConfigData('catalog.products.product_view_page.no_of_related_products'))
            ->get();

        return ProductResource::collection($relatedProducts);
    }

    /**
     * Up-sell product listings.
     *
     * @param  int  $id
     */
    public function upSellProducts($id): JsonResource
    {
        $product = $this->productRepository->findOrFail($id);

        $upSellProducts = $product->up_sells()
            ->take(core()->getConfigData('catalog.products.product_view_page.no_of_up_sells_products'))
            ->get();

        return ProductResource::collection($upSellProducts);
    }
}
