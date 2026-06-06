<?php

namespace Webkul\Installer\Database\Seeders\Category;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    public function run($parameters = [])
    {
        DB::table('categories')->delete();
        DB::table('category_translations')->delete();

        $now = Carbon::now();
        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');

        DB::table('categories')->insert([
            [
                'id' => 1,
                'position' => 1,
                'logo_path' => null,
                'status' => 1,
                '_lft' => 1,
                '_rgt' => 14,
                'parent_id' => null,
                'banner_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $locales = $parameters['allowed_locales'] ?? [$defaultLocale];

        foreach ($locales as $locale) {
            DB::table('category_translations')->insert([
                [
                    'name' => trans('installer::app.seeders.category.categories.name', [], $locale),
                    'slug' => 'root',
                    'description' => trans('installer::app.seeders.category.categories.description', [], $locale),
                    'meta_title' => '',
                    'meta_description' => '',
                    'meta_keywords' => '',
                    'category_id' => '1',
                    'locale' => $locale,
                ],
            ]);
        }
    }

    public function sampleCategories(array $parameters = [])
    {
        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');
        $now = Carbon::now();
        $locales = ['en', 'ar'];

        $categories = [
            ['id' => 2, 'slug' => 'action-figures', 'name_en' => 'Action Figures', 'name_ar' => 'مجسمات وأكشن', '_lft' => 2, '_rgt' => 3],
            ['id' => 3, 'slug' => 'dolls', 'name_en' => 'Dolls', 'name_ar' => 'عرائس ودُمى', '_lft' => 4, '_rgt' => 5],
            ['id' => 4, 'slug' => 'cars', 'name_en' => 'Cars & Vehicles', 'name_ar' => 'سيارات ومركبات', '_lft' => 6, '_rgt' => 7],
            ['id' => 5, 'slug' => 'puzzles', 'name_en' => 'Puzzles & Board Games', 'name_ar' => 'ألعاب ذكاء ولوحية', '_lft' => 8, '_rgt' => 9],
            ['id' => 6, 'slug' => 'arts', 'name_en' => 'Arts & Crafts', 'name_ar' => 'رسم وأشغال يدوية', '_lft' => 10, '_rgt' => 11],
            ['id' => 7, 'slug' => 'sports', 'name_en' => 'Sports & Outdoors', 'name_ar' => 'ألعاب رياضية وخارجية', '_lft' => 12, '_rgt' => 13],
        ];

        foreach ($categories as $idx => $cat) {
            DB::table('categories')->insert([
                'id' => $cat['id'],
                'position' => $idx + 1,
                'logo_path' => null,
                'status' => 1,
                'display_mode' => 'products_and_description',
                '_lft' => $cat['_lft'],
                '_rgt' => $cat['_rgt'],
                'parent_id' => 1,
                'additional' => null,
                'banner_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($locales as $locale) {
                DB::table('category_translations')->insert([
                    'category_id' => $cat['id'],
                    'name' => $locale == 'ar' ? $cat['name_ar'] : $cat['name_en'],
                    'slug' => $cat['slug'],
                    'url_path' => $cat['slug'],
                    'description' => '',
                    'meta_title' => '',
                    'meta_description' => '',
                    'meta_keywords' => '',
                    'locale_id' => null,
                    'locale' => $locale,
                ]);
            }
        }
    }
}
