<?php

namespace Webkul\Installer\Database\Seeders\Shop;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ThemeCustomizationTableSeeder extends Seeder
{
    /**
     * Base path for the images.
     */
    const BASE_PATH = 'packages/Webkul/Installer/src/Resources/assets/images/seeders/theme/';

    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('theme_customizations')->delete();

        DB::table('theme_customization_translations')->delete();

        $now = Carbon::now();

        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');

        $appUrl = config('app.url');

        DB::table('theme_customizations')
            ->insert([
                [
                    'id' => 1,
                    'type' => 'image_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.image-carousel.name', [], $defaultLocale),
                    'sort_order' => 1,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 2,
                    'type' => 'static_content',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.main-groups.name', [], $defaultLocale),
                    'sort_order' => 2,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 3,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.new-arrivals-carousel.name', [], $defaultLocale),
                    'sort_order' => 3,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 4,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.featured-products.name', [], $defaultLocale),
                    'sort_order' => 4,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 5,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.all-products.name', [], $defaultLocale),
                    'sort_order' => 5,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 6,
                    'type' => 'footer_links',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.footer-links.name', [], $defaultLocale),
                    'sort_order' => 11,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 7,
                    'type' => 'services_content',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.services-content.name', [], $defaultLocale),
                    'sort_order' => 12,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

        $locales = $parameters['allowed_locales'] ?? ['en', 'ar'];
        // Always ensure both languages are seeded so the admin panel shows correct text in both locales
        $locales = array_unique(array_merge($locales, ['en', 'ar']));

        foreach ($locales as $locale) {
            DB::table('theme_customization_translations')
                ->insert([
                    [
                        'theme_customization_id' => 1,

                        'locale' => $locale,

                        'options' => json_encode([
                            'images' => [
                                [
                                    'title' => '',
                                    'link' => '',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/1.png', 'sliders/en/1.png'),
                                ], [
                                    'title' => '',
                                    'link' => '',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/2.png', 'sliders/en/2.png'),
                                ], [
                                    'title' => '',
                                    'link' => '',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/3.png', 'sliders/en/3.png'),
                                ], [
                                    'title' => '',
                                    'link' => '',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/4.png', 'sliders/en/4.png'),
                                ], [
                                    'title' => '',
                                    'link' => '',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/5.png', 'sliders/en/5.png'),
                                ], [
                                    'title' => '',
                                    'link' => '',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/6.png', 'sliders/en/6.png'),
                                ],
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 2,

                        'locale' => $locale,

                        'options' => json_encode([
                            'html' => $this->buildGroupsHtml($locale),
                            'css'  => '.groups-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;text-align:center;padding:28px 16px;}.groups-inner{display:inline-flex;flex-direction:row;gap:28px;}.group-card a{display:flex;flex-direction:column;align-items:center;gap:10px;text-decoration:none;color:inherit;}.group-card .g-img{width:104px;height:104px;transition:transform .25s ease;}.group-card:hover .g-img{transform:scale(1.07);}.group-card span{font-size:14px;font-weight:700;color:#222;}.groups-wrap::-webkit-scrollbar{height:4px;}.groups-wrap::-webkit-scrollbar-track{background:#f1f1f1;}.groups-wrap::-webkit-scrollbar-thumb{background:#ccc;border-radius:2px;}',
                        ]),
                    ], [
                        'theme_customization_id' => 3,

                        'locale' => $locale,

                        'options' => json_encode([
                            'title' => trans('installer::app.seeders.shop.theme-customizations.new-arrivals-carousel.name', [], $locale),
                            'filters' => [
                                'sort' => 'created_at-desc',
                                'limit' => 10,
                                'new' => 1,
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 4,

                        'locale' => $locale,

                        'options' => json_encode([
                            'title' => trans('installer::app.seeders.shop.theme-customizations.featured-products.name', [], $locale),
                            'filters' => [
                                'sort' => 'created_at-desc',
                                'limit' => 10,
                                'featured' => 1,
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 5,

                        'locale' => $locale,

                        'options' => json_encode([
                            'title' => trans('installer::app.seeders.shop.theme-customizations.all-products.name', [], $locale),
                            'filters' => [
                                'sort' => 'created_at-desc',
                                'limit' => 12,
                            ],
                        ]),
                    ],[
                        'theme_customization_id' => 6,

                        'locale' => $locale,

                        'options' => json_encode([
                            'column_1' => [
                                [
                                    'url' => '/page/about-us',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.about-us', [], $locale),
                                    'sort_order' => 1,
                                ], [
                                    'url' => '/contact-us',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.contact-us', [], $locale),
                                    'sort_order' => 2,
                                ], [
                                    'url' => '/page/customer-service',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.customer-service', [], $locale),
                                    'sort_order' => 3,
                                ], [
                                    'url' => '/page/whats-new',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.whats-new', [], $locale),
                                    'sort_order' => 4,
                                ], [
                                    'url' => '/page/terms-of-use',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.terms-of-use', [], $locale),
                                    'sort_order' => 5,
                                ], [
                                    'url' => '/page/terms-conditions',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.terms-conditions', [], $locale),
                                    'sort_order' => 6,
                                ],
                            ],

                            'column_2' => [
                                [
                                    'url' => '/page/privacy-policy',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.privacy-policy', [], $locale),
                                    'sort_order' => 1,
                                ], [
                                    'url' => '/page/payment-policy',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.payment-policy', [], $locale),
                                    'sort_order' => 2,
                                ], [
                                    'url' => '/page/shipping-policy',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.shipping-policy', [], $locale),
                                    'sort_order' => 3,
                                ], [
                                    'url' => '/page/refund-policy',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.refund-policy', [], $locale),
                                    'sort_order' => 4,
                                ], [
                                    'url' => '/page/return-policy',
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.footer-links.options.return-policy', [], $locale),
                                    'sort_order' => 5,
                                ],
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 7,

                        'locale' => $locale,

                        'options' => json_encode([
                            'services' => [
                                [
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.services-content.title.free-shipping', [], $locale),
                                    'description' => trans('installer::app.seeders.shop.theme-customizations.services-content.description.free-shipping-info', [], $locale),
                                    'service_icon' => 'icon-truck',
                                ], [
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.services-content.title.product-replace', [], $locale),
                                    'description' => trans('installer::app.seeders.shop.theme-customizations.services-content.description.product-replace-info', [], $locale),
                                    'service_icon' => 'icon-product',
                                ], [
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.services-content.title.emi-available', [], $locale),
                                    'description' => trans('installer::app.seeders.shop.theme-customizations.services-content.description.emi-available-info', [], $locale),
                                    'service_icon' => 'icon-dollar-sign',
                                ], [
                                    'title' => trans('installer::app.seeders.shop.theme-customizations.services-content.title.time-support', [], $locale),
                                    'description' => trans('installer::app.seeders.shop.theme-customizations.services-content.description.time-support-info', [], $locale),
                                    'service_icon' => 'icon-support',
                                ],
                            ],
                        ]),
                    ],
                ]);
        }
    }

    /**
     * Seed sample theme customizations for demo products.
     *
     * @param  array  $parameters
     * @return void
     */
    public function sampleThemeCustomizations($parameters = [])
    {
        $now = Carbon::now();

        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');

        DB::table('theme_customizations')
            ->insert([
                [
                    'id' => 9,
                    'type' => 'category_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.categories-collections.name', [], $defaultLocale),
                    'sort_order' => 3,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 10,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.mens-collection.name', [], $defaultLocale),
                    'sort_order' => 4,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 11,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.womens-collection.name', [], $defaultLocale),
                    'sort_order' => 7,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 12,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.kids-collection.name', [], $defaultLocale),
                    'sort_order' => 9,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 13,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.book-tickets.name', [], $defaultLocale),
                    'sort_order' => 13,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

        $locales = $parameters['allowed_locales'] ?? [$defaultLocale];

        foreach ($locales as $locale) {
            DB::table('theme_customization_translations')
                ->insert([
                    [
                        'theme_customization_id' => 9,

                        'locale' => $locale,

                        'options' => json_encode([
                            'filters' => [
                                'parent_id' => 1,
                                'sort' => 'asc',
                                'limit' => 10,
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 10,

                        'locale' => $locale,

                        'options' => json_encode([
                            'title' => trans('installer::app.seeders.shop.theme-customizations.mens-collection.options.title', [], $locale),

                            'filters' => [
                                'category_id' => 2,
                                'sort' => 'created_at-desc',
                                'limit' => 10,
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 11,

                        'locale' => $locale,

                        'options' => json_encode([
                            'title' => trans('installer::app.seeders.shop.theme-customizations.womens-collection.options.title', [], $locale),

                            'filters' => [
                                'category_id' => 4,
                                'sort' => 'created_at-desc',
                                'limit' => 10,
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 12,

                        'locale' => $locale,

                        'options' => json_encode([
                            'title' => trans('installer::app.seeders.shop.theme-customizations.kids-collection.options.title', [], $locale),

                            'filters' => [
                                'category_id' => 3,
                                'sort' => 'price-desc',
                                'limit' => 10,
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 13,

                        'locale' => $locale,

                        'options' => json_encode([
                            'title' => trans('installer::app.seeders.shop.theme-customizations.book-tickets.options.title', [], $locale),

                            'filters' => [
                                'category_id' => 26,
                                'sort' => 'name-asc',
                                'limit' => 30,
                            ],
                        ]),
                    ],
                ]);
        }

        $this->updateLinksForSampleProducts();
    }

    /**
     * Update theme customization links to point to actual category slugs.
     *
     * Called after sample categories are seeded so the links resolve correctly.
     */
    public function updateLinksForSampleProducts(): void
    {
        $categorySlugs = [
            'formal-wear-female',
            'formal-wear-men',
            'active-wear-female',
            'smart-home-automation',
            'mobile-phones-accessories',
            'laptops-tablets',
            'electronics',
            'mens',
            'womens',
            'wellness',
            'active-wear',
        ];

        DB::table('theme_customization_translations')
            ->whereIn('theme_customization_id', [1, 3, 4, 5, 6])
            ->get()
            ->each(function ($translation) use ($categorySlugs) {
                $options = $translation->options;

                foreach ($categorySlugs as $slug) {
                    $options = str_replace('#'.$slug, $slug, $options);
                }

                DB::table('theme_customization_translations')
                    ->where('id', $translation->id)
                    ->update(['options' => $options]);
            });
    }

    /**
     * Store image in storage.
     *
     * @return void
     */
    public function storeFileIfExists($targetPath, $file, $default = null)
    {
        if (file_exists(base_path(self::BASE_PATH.$file))) {
            return 'storage/'.Storage::putFile($targetPath, new File(base_path(self::BASE_PATH.$file)));
        }

        if (! $default) {
            return;
        }

        if (file_exists(base_path(self::BASE_PATH.$default))) {
            return 'storage/'.Storage::putFile($targetPath, new File(base_path(self::BASE_PATH.$default)));
        }
    }

    /**
     * Store a group swatch image from the ProductGroupsTableSeeder data directory.
     *
     * @param  string  $targetPath  Storage sub-path (e.g. 'theme/2/groups')
     * @param  string  $filename    Image filename (e.g. 'toys.png')
     * @return string|null
     */
    public function storeGroupImage(string $targetPath, string $filename): ?string
    {
        $groupImagesDir = 'packages/Webkul/Installer/src/Database/Seeders/Product/Data/group-images/';
        $fullPath = base_path($groupImagesDir.$filename);

        if (file_exists($fullPath)) {
            return 'storage/'.Storage::putFile($targetPath, new File($fullPath));
        }

        return null;
    }

    /**
     * Build the groups horizontal row HTML for the static_content theme block.
     * Images are 104 px circles, centered, with bilingual labels.
     */
    private function buildGroupsHtml(string $locale): string
    {
        $isAr = $locale === 'ar';

        $groups = [
            ['ar' => 'العاب',          'en' => 'Toys',          'slug' => 'toys',          'file' => 'toys.png'],
            ['ar' => 'وصل حديثا',      'en' => 'New Arrivals',  'slug' => 'new-arrivals',  'file' => 'new-arrivals.png'],
            ['ar' => 'تعليمية',        'en' => 'Educational',   'slug' => 'educational',   'file' => 'educational.png'],
            ['ar' => 'هدايا',          'en' => 'Gifts',         'slug' => 'gifts',         'file' => 'gifts.png'],
            ['ar' => 'اقل من 1 دينار', 'en' => 'Under 1 Dinar', 'slug' => 'under-1-dinar', 'file' => 'under-1-dinar.png'],
            ['ar' => 'ترفيه',          'en' => 'Entertainment', 'slug' => 'entertainment', 'file' => 'entertainment.png'],
            ['ar' => 'رياضة',          'en' => 'Sports',        'slug' => 'sports',        'file' => 'sports.png'],
            ['ar' => 'عروض',           'en' => 'Offers',        'slug' => 'offers',        'file' => 'offers.png'],
        ];

        $cards = '';
        foreach ($groups as $group) {
            $label = $isAr ? $group['ar'] : $group['en'];
            $img   = $this->storeGroupImage('theme/2/groups', $group['file']) ?? '';
            $href  = '/products?group='.$group['slug'];

            $cards .= '<div class="group-card">'
                .'<a href="'.$href.'">'
                .'<div class="g-img">'
                .'<img src="'.$img.'" width="104" height="104" alt="'.htmlspecialchars($label).'" style="width:100%;height:100%;object-fit:cover;">'
                .'</div>'
                .'<span>'.htmlspecialchars($label).'</span>'
                .'</a>'
                .'</div>';
        }

        return '<div class="groups-wrap"><div class="groups-inner">'.$cards.'</div></div>';
    }
}
