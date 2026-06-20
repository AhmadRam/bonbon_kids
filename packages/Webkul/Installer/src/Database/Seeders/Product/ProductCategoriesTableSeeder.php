<?php

namespace Webkul\Installer\Database\Seeders\Product;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Category\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class ProductCategoriesTableSeeder extends Seeder
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function run()
    {
        $root = $this->categoryRepository->findOneByField('parent_id', null);
        if (! $root) {
            if (isset($this->command)) {
                $this->command->error('Root category not found!');
            }

            return;
        }

        // Delete existing sample categories if any exist to start fresh
        $children = $this->categoryRepository->findWhere(['parent_id' => $root->id]);
        foreach ($children as $child) {
            try {
                $this->categoryRepository->delete($child->id);
            } catch (\Exception $e) {
                // Ignore exceptions during cascade deletion
            }
        }

        $categoriesData = [
            ['ar' => 'ألعاب تعليمية', 'en' => 'Educational Toys', 'file' => 'educational.png', 'status' => 1],
            ['ar' => 'ألعاب خارجية', 'en' => 'Outdoor Toys', 'file' => 'outdoor.png', 'status' => 1],
            ['ar' => 'ألعاب ذكية', 'en' => 'Smart Toys', 'file' => 'smart.png', 'status' => 1],
            ['ar' => 'ألعاب منزلية', 'en' => 'Indoor Toys', 'file' => 'indoor.png', 'status' => 1],
            ['ar' => 'ألعاب العرائس', 'en' => 'Dolls Toys', 'file' => 'dolls.png', 'status' => 0],
            ['ar' => 'ألعاب سيارات', 'en' => 'Cars Toys', 'file' => 'cars.png', 'status' => 0],
            ['ar' => 'ألعاب أقل من دينار', 'en' => 'Under 1 Dinar', 'file' => 'under-1-dinar.png', 'status' => 1],
            ['ar' => 'عروض وخصومات', 'en' => 'Offers & Discounts', 'file' => 'offers.png', 'status' => 1],
        ];

        foreach ($categoriesData as $index => $data) {
            $slug = Str::slug($data['en']);
            
            $category = $this->categoryRepository->create([
                'status'       => $data['status'],
                'position'     => $index + 1,
                'display_mode' => 'products_and_description',
                'parent_id'    => $root->id,
            ]);

            // Try to copy category image from category-images directory if exists
            $imagePath = __DIR__ . '/Data/category-images/' . $data['file'];
            if (file_exists($imagePath)) {
                $storedPath = Storage::putFile('category/' . $category->id, new File($imagePath));
                $category->logo_path = $storedPath;
                $category->save();
            }

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'en'],
                [
                    'name'        => $data['en'],
                    'slug'        => $slug,
                    'description' => $data['en'],
                ]
            );

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'ar'],
                [
                    'name'        => $data['ar'],
                    'slug'        => $slug,
                    'description' => $data['ar'],
                ]
            );
        }
    }
}
