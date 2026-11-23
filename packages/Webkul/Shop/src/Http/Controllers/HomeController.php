<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Requests\ContactRequest;
use Webkul\Shop\Http\Resources\CategoryTreeResource;
use Webkul\Shop\Mail\ContactUs;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class HomeController extends Controller
{
    /**
     * Using const variable for status
     */
    const STATUS = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ThemeCustomizationRepository $themeCustomizationRepository,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Loads the home page for the storefront.
     *
     * @return View
     */
    public function index()
    {
        $customizations = $this->themeCustomizationRepository->orderBy('sort_order')->findWhere([
            'status' => self::STATUS,
            'channel_id' => core()->getCurrentChannel()->id,
            'theme_code' => core()->getCurrentChannel()->theme,
        ]);

        $categories = $this->categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);

        $categories = CategoryTreeResource::collection($categories);

        $carouselExcludeIds = $this->resolveCarouselExcludeIds($customizations);

        return view('shop::home.index', compact('customizations', 'categories', 'carouselExcludeIds'));
    }

    /**
     * Compute per-carousel product IDs to exclude so products do not repeat across homepage carousels.
     */
    public function resolveCarouselExcludeIds($customizations): array
    {
        $productCarousels = $customizations
            ->filter(fn ($customization) => $customization->type === $customization::PRODUCT_CAROUSEL)
            ->values();

        $carouselExcludeIds = [];

        if ($productCarousels->isEmpty()) {
            return $carouselExcludeIds;
        }

        $searchEngine = 'database';

        if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
            $searchEngine = core()->getConfigData('catalog.products.search.storefront_mode');
        }

        $channelId = core()->getCurrentChannel()->id;
        $seenProductIds = [];
        $lastIndex = $productCarousels->count() - 1;

        foreach ($productCarousels as $index => $customization) {
            $carouselExcludeIds[$customization->id] = $seenProductIds;

            if ($index === $lastIndex) {
                continue;
            }

            $filters = $customization->options['filters'] ?? [];

            $queryParams = array_merge($filters, [
                'channel_id' => $channelId,
                'status' => 1,
                'visible_individually' => 1,
            ]);

            if (! empty($seenProductIds)) {
                $queryParams['exclude_ids'] = implode(',', $seenProductIds);
            }

            $products = $this->productRepository
                ->setSearchEngine($searchEngine)
                ->getAll($queryParams);

            $seenProductIds = array_values(array_unique(array_merge(
                $seenProductIds,
                $products->pluck('id')->all()
            )));
        }

        return $carouselExcludeIds;
    }

    /**
     * Loads the home page for the storefront if something wrong.
     *
     * @return \Exception
     */
    public function notFound()
    {
        abort(404);
    }

    /**
     * Summary of contact.
     *
     * @return View
     */
    public function contactUs()
    {
        return view('shop::home.contact-us');
    }

    /**
     * Summary of store.
     *
     * @return RedirectResponse
     */
    public function sendContactUsMail(ContactRequest $contactRequest)
    {
        try {
            Mail::queue(new ContactUs($contactRequest->only([
                'name',
                'email',
                'contact',
                'message',
            ])));

            session()->flash('success', trans('shop::app.home.thanks-for-contact'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            report($e);
        }

        return back();
    }
}
