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
        $slugs = [
            '0-2' => '0-2.png',
            '3-4' => '3-4.png',
            '5-7' => '5-7.png',
            '8-10' => '8-10.png',
            '11-12' => '11-12.png',
            '13+' => '13+.png',
        ];

        $root = $this->categoryRepository->findOneByField('parent_id', null);
        if (! $root) {
            if (isset($this->command)) {
                $this->command->error('Root category not found!');
            }

            return;
        }

        $now = Carbon::now();

        foreach ($slugs as $slug => $imageName) {
            $existingTranslation = DB::table('category_translations')
                ->where('slug', $slug)
                ->where('locale', 'en')
                ->first();

            if ($existingTranslation) {
                continue;
            }

            $category = $this->categoryRepository->create([
                'status' => 1,
                'position' => 1,
                'display_mode' => 'products_and_description',
                'parent_id' => $root->id,
            ]);

            // Copy category image if exists
            $imagePath = __DIR__ . '/Data/category-images/' . $imageName;
            if (file_exists($imagePath)) {
                $storedPath = Storage::putFile('category/' . $category->id, new File($imagePath));
                $category->logo_path = $storedPath;
                $category->save();
            }

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'en'],
                ['name' => $slug, 'slug' => $slug, 'description' => $slug]
            );

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'ar'],
                ['name' => $slug, 'slug' => $slug, 'description' => $slug]
            );
        }
    }
}
