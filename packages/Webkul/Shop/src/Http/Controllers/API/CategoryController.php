<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Attribute\Enums\AttributeTypeEnum;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Resources\AttributeOptionResource;
use Webkul\Shop\Http\Resources\AttributeResource;
use Webkul\Shop\Http\Resources\CategoryResource;
use Webkul\Shop\Http\Resources\CategoryTreeResource;

class CategoryController extends APIController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Get all categories.
     */
    public function index(): JsonResource
    {
        /**
         * These are the default parameters. By default, only the enabled category
         * will be shown in the current locale.
         */
        $defaultParams = [
            'status' => 1,
            'locale' => app()->getLocale(),
        ];

        $categories = $this->categoryRepository->getAll(array_merge($defaultParams, request()->all()));

        return CategoryResource::collection($categories);
    }

    /**
     * Get all categories in tree format.
     */
    public function tree(): JsonResource
    {
        $categories = $this->categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);

        return CategoryTreeResource::collection($categories);
    }

    /**
     * Get filterable attributes for category.
     */
    public function getAttributes(): JsonResource
    {
        if (! request('category_id')) {
            $filterableAttributes = $this->attributeRepository->getFilterableAttributes();
        } else {
            $category = $this->categoryRepository->findOrFail(request('category_id'));
            $filterableAttributes = $category->filterableAttributes;

            if ($filterableAttributes->isEmpty()) {
                $filterableAttributes = $this->attributeRepository->getFilterableAttributes();
            }
        }

        $filterableAttributes = collect($filterableAttributes->all());
        
        $categoryAttribute = new \Webkul\Attribute\Models\Attribute([
            'id' => 999999,
            'code' => 'category_id',
            'type' => 'select',
            'admin_name' => app()->getLocale() == 'ar' ? 'الفئات' : 'Categories',
        ]);
        
        $filterableAttributes->prepend($categoryAttribute);

        return AttributeResource::collection($filterableAttributes);
    }

    /**
     * Get attribute options with pagination and search.
     */
    public function getAttributeOptions(int $attributeId): mixed
    {
        if ($attributeId === 999999) {
            $query = $this->categoryRepository->getModel()->where('parent_id', 1);

            if ($search = request('search')) {
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $query->orderBy('position');

            $categories = $query->paginate();

            $options = $categories->getCollection()->map(function ($category) {
                return new \Webkul\Attribute\Models\AttributeOption([
                    'id' => $category->id,
                    'admin_name' => $category->name,
                    'sort_order' => $category->position,
                ]);
            });
            $categories->setCollection($options);

            return AttributeOptionResource::collection($categories);
        }

        $attribute = $this->attributeRepository->findOrFail($attributeId);

        if ($attribute->type === AttributeTypeEnum::BOOLEAN->value) {
            return new JsonResponse([
                'data' => AttributeTypeEnum::getBooleanOptions(),
            ]);
        }

        $query = $attribute->options()
            ->with([
                'translation' => fn ($query) => $query->where('locale', core()->getCurrentLocale()->code),
            ]);

        if ($search = request('search')) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('translation', fn ($query) => $query->where('label', 'like', "%{$search}%"))
                    ->orWhere('admin_name', 'like', "%{$search}%");
            });
        }

        if ($categoryId = request('category_id')) {
            $query->whereExists(function ($query) use ($attribute, $categoryId) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('product_attribute_values')
                    ->join('products', 'products.id', '=', 'product_attribute_values.product_id')
                    ->join('product_categories', 'product_categories.product_id', '=', 'products.id')
                    ->where('product_attribute_values.attribute_id', $attribute->id)
                    ->where('product_categories.category_id', $categoryId)
                    ->where(function ($q) {
                        $q->whereColumn('product_attribute_values.integer_value', 'attribute_options.id')
                          ->orWhereRaw('FIND_IN_SET(attribute_options.id, product_attribute_values.text_value)');
                    });
            });
        }

        $query->orderBy('sort_order');

        return AttributeOptionResource::collection($query->paginate());
    }

    /**
     * Get product maximum price.
     */
    public function getProductMaxPrice($categoryId = null): JsonResource
    {
        if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
            $searchEngine = core()->getConfigData('catalog.products.search.storefront_mode');
        }

        $maxPrice = $this->productRepository
            ->setSearchEngine($searchEngine ?? 'database')
            ->getMaxPrice(['category_id' => $categoryId]);

        return new JsonResource([
            'max_price' => core()->convertPrice($maxPrice),
        ]);
    }
}
