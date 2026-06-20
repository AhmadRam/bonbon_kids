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
                    'sort_order' => 4,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 3,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.new-arrivals-carousel.name', [], $defaultLocale),
                    'sort_order' => 6,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 14,
                    'type' => 'static_content',
                    'name' => 'Age Groups',
                    'sort_order' => 3,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 15,
                    'type' => 'static_content',
                    'name' => 'Suitable For',
                    'sort_order' => 2,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 4,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.featured-products.name', [], $defaultLocale),
                    'sort_order' => 5,
                    'status' => 1,
                    'channel_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [
                    'id' => 5,
                    'type' => 'product_carousel',
                    'name' => trans('installer::app.seeders.shop.theme-customizations.all-products.name', [], $defaultLocale),
                    'sort_order' => 7,
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
                                    'link' => '/products',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/1.png', 'sliders/en/1.png'),
                                ], [
                                    'title' => '',
                                    'link' => '/smart-toys',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/2.png', 'sliders/en/2.png'),
                                ], [
                                    'title' => '',
                                    'link' => '/indoor-toys',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/3.png', 'sliders/en/3.png'),
                                ], [
                                    'title' => '',
                                    'link' => '/educational-toys',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/4.png', 'sliders/en/4.png'),
                                ], [
                                    'title' => '',
                                    'link' => '/under-1-dinar',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/5.png', 'sliders/en/5.png'),
                                ], [
                                    'title' => '',
                                    'link' => '/offers-discounts',
                                    'image' => $this->storeFileIfExists('theme/1', 'sliders/'.$locale.'/6.png', 'sliders/en/6.png'),
                                ],
                            ],
                        ]),
                    ], [
                        'theme_customization_id' => 2,

                        'locale' => $locale,

                        'options' => json_encode([
                            'html' => $this->buildGroupsHtml($locale),
                            'css'  => '@media (max-width: 768px) { .groups-wrap > div:first-child { margin-bottom: 24px !important; gap: 8px !important; } .groups-wrap > div:first-child > div[style*="border-bottom"] { max-width: 40px !important; border-bottom-width: 2px !important; } .groups-wrap > div:first-child > span { font-size: 18px !important; } .groups-wrap > div:first-child > h2 { font-size: 20px !important; text-align: center !important; } .groups-circles { justify-content: center !important; gap: 12px !important; margin-bottom: 20px !important; } .groups-circles .group-card { width: 22% !important; max-width: 80px !important; } .groups-circles .group-card a { gap: 6px !important; } .groups-circles .group-card span { font-size: 11px !important; line-height: 1.2 !important; } .groups-rects { display: flex !important; flex-wrap: wrap !important; justify-content: space-between !important; gap: 12px !important; } .groups-rects .group-card-rect { width: 48% !important; min-width: 0 !important; max-width: 100% !important; } .groups-rects .g-img-rect { height: auto !important; } }',
                        ]),
                    ], [
                        'theme_customization_id' => 14,

                        'locale' => $locale,

                        'options' => json_encode([
                            'html' => $this->buildAgeGroupsHtml($locale),
                            'css'  => '@media (max-width: 768px) { .groups-wrap > div:first-child { margin-bottom: 24px !important; gap: 8px !important; } .groups-wrap > div:first-child > div[style*="border-bottom"] { max-width: 40px !important; border-bottom-width: 2px !important; } .groups-wrap > div:first-child > span { font-size: 18px !important; } .groups-wrap > div:first-child > h2 { font-size: 20px !important; text-align: center !important; } .groups-wrap .groups-inner { gap: 10px !important; justify-content: center !important; } .age-pill a { padding: 10px 20px !important; font-size: 15px !important; border-radius: 20px !important; } }',
                        ]),
                    ], [
                        'theme_customization_id' => 15,

                        'locale' => $locale,

                        'options' => json_encode([
                            'html' => $this->buildSuitableForHtml($locale),
                            'css'  => '@media (max-width: 768px) { .groups-wrap > div:first-child { margin-bottom: 24px !important; gap: 8px !important; } .groups-wrap > div:first-child > div[style*="border-bottom"] { max-width: 40px !important; border-bottom-width: 2px !important; } .groups-wrap > div:first-child > span { font-size: 18px !important; } .groups-wrap > div:first-child > h2 { font-size: 20px !important; text-align: center !important; } .groups-wrap .groups-inner { display: flex !important; flex-wrap: wrap !important; justify-content: space-between !important; gap: 12px !important; } .group-card-banner { width: 48% !important; min-width: 0 !important; max-width: 100% !important; } .group-card-banner .g-img-banner { height: auto !important; } }',
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
    public function storeGroupImage(string $targetPath, string $filename, string $sourceDir = 'category-images'): ?string
    {
        $imagesDir = 'packages/Webkul/Installer/src/Database/Seeders/Product/Data/' . $sourceDir . '/';
        $fullPath = base_path($imagesDir.$filename);

        if (file_exists($fullPath)) {
            return 'storage/'.Storage::putFile($targetPath, new File($fullPath));
        }

        return null;
    }

    private function buildGroupsHtml(string $locale): string
    {
        $isAr = $locale === 'ar';
        $title = $isAr ? 'تسوق من خلال الفئات' : 'Shop by Categories';

        $circleGroups = [
            ['ar' => 'ألعاب تعليمية', 'en' => 'Educational Toys', 'slug' => 'educational-toys', 'file' => 'educational.png'],
            ['ar' => 'ألعاب خارجية', 'en' => 'Outdoor Toys',     'slug' => 'outdoor-toys',     'file' => 'outdoor.png'],
            ['ar' => 'ألعاب ذكية',    'en' => 'Smart Toys',       'slug' => 'smart-toys',       'file' => 'smart.png'],
            ['ar' => 'ألعاب منزلية',  'en' => 'Indoor Toys',      'slug' => 'indoor-toys',      'file' => 'indoor.png'],
        ];

        $rectGroups = [
            ['ar' => 'ألعاب أقل من دينار', 'en' => 'Under 1 Dinar',    'slug' => 'under-1-dinar',    'file' => 'under-1-dinar.png'],
            ['ar' => 'عروض وخصومات',       'en' => 'Offers & Discounts', 'slug' => 'offers-discounts', 'file' => 'offers.png'],
        ];

        $cardsHtml = '<div class="groups-circles" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; margin-bottom: 30px; width: 100%;">';
        foreach ($circleGroups as $group) {
            $label = $isAr ? $group['ar'] : $group['en'];
            $img   = $this->storeGroupImage('theme/2/groups', $group['file']) ?? '';
            $href  = '/' . $group['slug'];

            $cardsHtml .= '<div class="group-card" style="width: 23%; max-width: 250px;">'
                .'<a href="'.$href.'" style="display:flex; flex-direction:column; align-items:center; gap:15px; text-decoration:none; color:inherit;">'
                .'<div class="g-img" style="width:100%; aspect-ratio:1; transition:transform .25s ease;">'
                .'<img src="'.$img.'" alt="'.htmlspecialchars($label).'" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">'
                .'</div>'
                .'<span style="font-size:20px;font-weight:700;color:#222; text-align:center;">'.htmlspecialchars($label).'</span>'
                .'</a>'
                .'</div>';
        }
        $cardsHtml .= '</div>';

        $cardsHtml .= '<div class="groups-rects" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px; width: 100%;">';
        foreach ($rectGroups as $group) {
            $label = $isAr ? $group['ar'] : $group['en'];
            $img   = $this->storeGroupImage('theme/2/groups', $group['file']) ?? '';
            $href  = '/' . $group['slug'];

            $cardsHtml .= '<div class="group-card-rect" style="width: 48%; min-width: 280px; max-width: 600px;">'
                .'<a href="'.$href.'" style="display:block; text-decoration:none; color:inherit;">'
                .'<div class="g-img-rect" style="width:100%; transition:transform .25s ease;">'
                .'<img src="'.$img.'" alt="'.htmlspecialchars($label).'" style="width:100%; height:auto; display:block; border-radius:16px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">'
                .'</div>'
                .'</a>'
                .'</div>';
        }
        $cardsHtml .= '</div>';

        $titleHtml = '<div style="display:flex; align-items:center; justify-content:center; gap: 15px; margin-bottom: 40px;">'
            .'<div style="flex-grow:1; max-width: 250px; border-bottom: 3px dotted #ffb3d9; opacity: 0.7;"></div>'
            .'<span style="color:#ffb3d9; font-size:26px;">★</span>'
            .'<h2 style="font-size: 32px; font-weight: 900; color: #17385c; margin: 0;">'.$title.'</h2>'
            .'<span style="color:#ffb3d9; font-size:26px;">★</span>'
            .'<div style="flex-grow:1; max-width: 250px; border-bottom: 3px dotted #ffb3d9; opacity: 0.7;"></div>'
            .'</div>';

        return '<div class="groups-wrap" style="padding: 28px 16px; text-align:center; width: 100%; max-width: 1200px; margin: 0 auto;">'.$titleHtml.$cardsHtml.'</div>';
    }

    private function buildAgeGroupsHtml(string $locale): string
    {
        $isAr = $locale === 'ar';
        $title = $isAr ? 'تسوق من خلال الأعمار' : 'Shop by Age';

        $ages = [
            ['id' => 10, 'label_en' => '0-2', 'label_ar' => "2-0"],
            ['id' => 11, 'label_en' => '3-4', 'label_ar' => "4-3"],
            ['id' => 12, 'label_en' => '5-7', 'label_ar' => "7-5"],
            ['id' => 13, 'label_en' => '8-10', 'label_ar' => "10-8"],
            ['id' => 17, 'label_en' => '11+', 'label_ar' => "11+"],
        ];

        $cards = '';
        foreach ($ages as $age) {
            $label = $isAr ? $age['label_ar'] : $age['label_en'];
            $href  = '/products?age_group='.$age['id'];

            $cards .= '<div class="age-pill">'
                .'<a href="'.$href.'" style="display:block; padding: 18px 60px; background-color: #0b51b7; color: #fff; text-decoration: none; border-radius: 40px; font-weight: bold; font-size: 24px; transition: opacity 0.2s; box-shadow: 0 4px 8px rgba(11, 81, 183, 0.3);">'
                .'<span dir="auto"><bdi>'.htmlspecialchars($label).'</bdi></span>'
                .'</a>'
                .'</div>';
        }

        $titleHtml = '<div style="display:flex; align-items:center; justify-content:center; gap: 15px; margin-bottom: 40px;">'
            .'<div style="flex-grow:1; max-width: 250px; border-bottom: 3px dotted #8cbcfc; opacity: 0.7;"></div>'
            .'<span style="color:#8cbcfc; font-size:26px;">★</span>'
            .'<h2 style="font-size: 32px; font-weight: 900; color: #17385c; margin: 0;">'.$title.'</h2>'
            .'<span style="color:#8cbcfc; font-size:26px;">★</span>'
            .'<div style="flex-grow:1; max-width: 250px; border-bottom: 3px dotted #8cbcfc; opacity: 0.7;"></div>'
            .'</div>';

        return '<div class="groups-wrap" style="padding: 32px 16px; text-align: center; width: 100%; max-width: 1200px; margin: 0 auto; background-color: #f7fbff; border-radius: 20px; margin-top: 20px; margin-bottom: 20px;">'.$titleHtml.'<div class="groups-inner" style="display:flex; flex-wrap:wrap; justify-content:center; gap: 40px;">'.$cards.'</div></div>';
    }

    private function buildSuitableForHtml(string $locale): string
    {
        $isAr = $locale === 'ar';
        $title = $isAr ? 'لمن تتسوق؟' : 'Who are you shopping for?';

        $audiences = [
            ['id' => 15, 'label_en' => 'Girls', 'label_ar' => 'بنات', 'file' => 'girls.png'],
            ['id' => 14, 'label_en' => 'Boys', 'label_ar' => 'أولاد', 'file' => 'boys.png'],
        ];

        $cards = '';
        foreach ($audiences as $aud) {
            $label = $isAr ? $aud['label_ar'] : $aud['label_en'];
            $img   = $this->storeGroupImage('theme/15/groups', $aud['file'], 'suitable-images') ?? '';
            $href  = '/products?suitable_for='.$aud['id'];

            $cards .= '<div class="group-card-banner" style="width: 48%; min-width: 280px; max-width: 600px;">'
                .'<a href="'.$href.'" style="display:block; text-decoration:none; color:inherit;">'
                .'<div class="g-img-banner" style="width:100%; transition:transform .25s ease;">'
                .'<img src="'.$img.'" alt="'.htmlspecialchars($label).'" style="width:100%; height:auto; display:block; border-radius:24px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">'
                .'</div>'
                .'</a>'
                .'</div>';
        }

        $titleHtml = '<div style="display:flex; align-items:center; justify-content:center; gap: 15px; margin-bottom: 40px;">'
            .'<div style="flex-grow:1; max-width: 250px; border-bottom: 3px dotted #ffb3d9; opacity: 0.7;"></div>'
            .'<span style="color:#ffb3d9; font-size:26px;">★</span>'
            .'<h2 style="font-size: 32px; font-weight: 900; color: #17385c; margin: 0;">'.$title.'</h2>'
            .'<span style="color:#ffb3d9; font-size:26px;">★</span>'
            .'<div style="flex-grow:1; max-width: 250px; border-bottom: 3px dotted #ffb3d9; opacity: 0.7;"></div>'
            .'</div>';

        return '<div class="groups-wrap" style="padding: 32px 16px; text-align: center; width: 100%; max-width: 1200px; margin: 0 auto; background-color: #fff5f9; border-radius: 20px; margin-bottom: 30px;">'.$titleHtml.'<div class="groups-inner" style="display:flex; flex-wrap:wrap; justify-content:space-between; gap: 20px;">'.$cards.'</div></div>';
    }
}
