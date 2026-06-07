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
        $slugsToDelete = ['boys-toys', 'smart-toys', 'educational-toys', 'toddlers-toys', 'under-1-dinar', 'girls-toys', 'outdoor-toys', 'offers', '0-2', '3-4', '5-7', '8-10', '11-12', '13+', '11+'];
        foreach ($slugsToDelete as $slugToDelete) {
            $translation = DB::table('category_translations')
                ->where('slug', $slugToDelete)
                ->first();

            if ($translation) {
                try {
                    $this->categoryRepository->delete($translation->category_id);
                } catch (\Exception $e) {
                    // Ignore exceptions during cascade deletion
                }
            }
        }

        $categoriesData = [
            ['ar' => 'ألعاب أولاد', 'en' => 'Boys Toys', 'file' => 'boys.png', 'status' => 1],
            ['ar' => 'ألعاب ذكية', 'en' => 'Smart Toys', 'file' => 'smart.png', 'status' => 0],
            ['ar' => 'ألعاب تعليمية', 'en' => 'Educational Toys', 'file' => 'educational.png', 'status' => 1],
            ['ar' => 'ألعاب مواليد', 'en' => 'Toddlers Toys', 'file' => 'toddlers.png', 'status' => 1],
            ['ar' => 'اقل من 1 دينار', 'en' => 'Under 1 Dinar', 'file' => 'under-1-dinar.png', 'status' => 1],
            ['ar' => 'ألعاب بنات', 'en' => 'Girls Toys', 'file' => 'girls.png', 'status' => 1],
            ['ar' => 'ألعاب خارجية', 'en' => 'Outdoor Toys', 'file' => 'outdoor.png', 'status' => 0],
            ['ar' => 'عروض', 'en' => 'Offers', 'file' => 'offers.png', 'status' => 1],
        ];

        foreach ($categoriesData as $index => $data) {
            $slug = Str::slug($data['en']);
            
            $category = $this->categoryRepository->create([
                'status'       => $data['status'],
                'position'     => $index + 1,
                'display_mode' => 'products_and_description',
                'parent_id'    => $root->id,
            ]);

            // Try to copy category image from previous group-images directory if exists
            $imagePath = __DIR__ . '/Data/group-images/' . $data['file'];
            if (!file_exists($imagePath)) {
                // fallback to category-images if they moved it
                $imagePath = __DIR__ . '/Data/category-images/' . $data['file'];
            }
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
