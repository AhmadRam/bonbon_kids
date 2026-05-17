<?php

namespace Webkul\Installer\Database\Seeders\Product;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Category\Repositories\CategoryRepository;

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

        // Delete existing age categories if any exist
        $slugsToDelete = ['0-2', '3-4', '5-7', '8-10', '11-12', '13+', '11+'];
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
            '0-2' => [
                'image'   => '0-2.png',
                'name_en' => '0-2',
                'name_ar' => '2-0',
            ],
            '3-4' => [
                'image'   => '3-4.png',
                'name_en' => '3-4',
                'name_ar' => '4-3',
            ],
            '5-7' => [
                'image'   => '5-7.png',
                'name_en' => '5-7',
                'name_ar' => '7-5',
            ],
            '8-10' => [
                'image'   => '8-10.png',
                'name_en' => '8-10',
                'name_ar' => '10-8',
            ],
            '11+' => [
                'image'   => '11+.png',
                'name_en' => '11+',
                'name_ar' => '+11',
            ],
        ];

        foreach ($categoriesData as $slug => $data) {
            $category = $this->categoryRepository->create([
                'status'       => 1,
                'position'     => 1,
                'display_mode' => 'products_and_description',
                'parent_id'    => $root->id,
            ]);

            // Copy category image if exists
            $imagePath = __DIR__ . '/Data/category-images/' . $data['image'];
            if (file_exists($imagePath)) {
                $storedPath = Storage::putFile('category/' . $category->id, new File($imagePath));
                $category->logo_path = $storedPath;
                $category->save();
            }

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'en'],
                [
                    'name'        => $data['name_en'],
                    'slug'        => $slug,
                    'description' => $data['name_en'],
                ]
            );

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'ar'],
                [
                    'name'        => $data['name_ar'],
                    'slug'        => $slug,
                    'description' => $data['name_ar'],
                ]
            );
        }
    }
}
