<?php

namespace Webkul\Installer\Database\Seeders\Product;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Attribute\Repositories\AttributeOptionTranslationRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImportData implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        return $array;
    }
}

class ProductsTableSeeder extends Seeder
{
    protected $productRepository;
    protected $productInventoryRepository;
    protected $categoryRepository;
    protected $attributeRepository;
    protected $attributeOptionRepository;
    protected $attributeOptionTranslationRepository;
    protected $productImageRepository;

    public function __construct(
        ProductRepository $productRepository,
        ProductInventoryRepository $productInventoryRepository,
        CategoryRepository $categoryRepository,
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository,
        AttributeOptionTranslationRepository $attributeOptionTranslationRepository,
        ProductImageRepository $productImageRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productInventoryRepository = $productInventoryRepository;
        $this->categoryRepository = $categoryRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->attributeOptionTranslationRepository = $attributeOptionTranslationRepository;
        $this->productImageRepository = $productImageRepository;
    }

    public function run()
    {
        Model::reguard();

        if (isset($this->command)) {
            $this->command->info("Starting Import Process from new Excel file...");
        }

        // 1. Delete old files
        $oldFiles = [
            'First patch invoice with pictures.xlsx',
            'Second patch invoice with pictures.xlsx'
        ];
        foreach ($oldFiles as $f) {
            if (file_exists(base_path($f))) {
                unlink(base_path($f));
                if (isset($this->command)) $this->command->info("Deleted old file: " . $f);
            }
        }

        // 2. Setup Categories
        if (isset($this->command)) $this->command->info("Setting up categories...");
        $categoriesMap = $this->setupCategories();

        // 3. Setup Groups
        if (isset($this->command)) $this->command->info("Setting up groups...");
        $groupsMap = $this->setupGroups();

        // 4. Parse Excel
        if (isset($this->command)) $this->command->info("Extracting data from hilal_ready_products_import_updated.xlsx...");
        $filePath = __DIR__ . '/Data/hilal_ready_products_import_updated.xlsx';
        if (!file_exists($filePath)) {
            if (isset($this->command)) $this->command->error("File not found: " . $filePath);
            return;
        }

        try {
            $data = Excel::toArray(new ProductImportData(), $filePath);
            $rows = $data[0] ?? [];
        } catch (\Exception $e) {
            if (isset($this->command)) $this->command->error("Failed to parse Excel file: " . $e->getMessage());
            return;
        }

        if (isset($this->command)) $this->command->info("Found " . count($rows) . " products. Starting import...");

        $addedCount = 0;
        $updatedCount = 0;

        foreach ($rows as $row) {
            $sku = trim((string)($row['sku'] ?? ''));
            if (empty($sku)) continue;

            $nameEn = $row['name_en'] ?? 'Product ' . $sku;
            $nameAr = $row['name_ar'] ?? $nameEn;
            $price = (float)($row['price'] ?? 0);
            $qty = (int)($row['inventories'] ?? 0);
            
            // Match Category
            $categorySlug = $row['categories_slug'] ?? '';
            $categoryId = $categoriesMap[$categorySlug] ?? null;

            // Match Group
            $groupName = $row['group'] ?? ($row['brand'] ?? ''); // Fallback to brand column if group column is not created yet
            $groupId = null;
            if (!empty($groupName)) {
                foreach ($groupsMap as $gId => $labels) {
                    if (in_array(trim($groupName), $labels)) {
                        $groupId = $gId;
                        break;
                    }
                }
            }

            // Create or Find Product
            $product = $this->productRepository->findOneByField('sku', $sku);
            $isNew = false;

            if (!$product) {
                try {
                    $product = $this->productRepository->create([
                        'type' => 'simple',
                        'attribute_family_id' => 1,
                        'sku' => $sku,
                    ]);
                    $isNew = true;
                } catch (\Exception $e) {
                    if (isset($this->command)) $this->command->error("Failed to create SKU: $sku - Error: " . $e->getMessage());
                    continue;
                }
            }

            // Generate unique URL key
            $urlKey = Str::slug($nameEn) . '-' . strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $sku));
            $existingUrl = app(\Webkul\Product\Repositories\ProductAttributeValueRepository::class)->findOneWhere([
                'attribute_id' => $this->attributeRepository->findOneByField('code', 'url_key')->id,
                'text_value' => $urlKey
            ]);
            if ($existingUrl && $existingUrl->product_id != $product->id) {
                $urlKey .= '-' . rand(1000, 9999);
            }

            $updateData = [
                'name' => $nameEn, // Default locale fallback
                'url_key' => $urlKey,
                'price' => $price,
                'cost' => (float)($row['cost'] ?? 0),
                'guest_checkout' => 1,
                'weight' => (float)($row['weight'] ?? 0),
                'status' => 1,
                'visible_individually' => 1,
                'channel' => 'default',
                'locale' => 'en',
                'channels' => [1],
                'categories' => $categoryId ? [$categoryId] : [],
                'description' => $row['description_en'] ?? '',
                'short_description' => $row['short_description_en'] ?? '',
                'dealer_price' => (float)($row['dealer_price'] ?? 0),
                'group' => $groupId,
                'brand' => null,
                'meta_title' => $row['meta_title_en'] ?? ($row['meta_title'] ?? ''),
                'meta_description' => $row['meta_description_en'] ?? ($row['meta_description'] ?? ''),
                'meta_keywords' => $row['meta_keywords_en'] ?? ($row['meta_keywords'] ?? '')
            ];

            try {
                // Save English Attributes
                $this->productRepository->update($updateData, $product->id);

                // Save Arabic Attributes
                $updateDataAr = $updateData;
                $updateDataAr['locale'] = 'ar';
                $updateDataAr['name'] = $nameAr;
                $updateDataAr['description'] = $row['description_ar'] ?? '';
                $updateDataAr['short_description'] = $row['short_description_ar'] ?? '';
                $updateDataAr['meta_title'] = $row['meta_title_ar'] ?? ($row['meta_title'] ?? '');
                $updateDataAr['meta_description'] = $row['meta_description_ar'] ?? ($row['meta_description'] ?? '');
                $updateDataAr['meta_keywords'] = $row['meta_keywords_ar'] ?? ($row['meta_keywords'] ?? '');
                
                // Using update again with 'ar' locale will insert/update the arabic translations in product_attribute_values
                $this->productRepository->update($updateDataAr, $product->id);

                if ($isNew) {
                    $addedCount++;
                } else {
                    $updatedCount++;
                }

                // Inventory
                $this->productInventoryRepository->saveInventories([
                    'inventories' => [1 => $qty]
                ], $product);

                // Images
                $extensions = ['jpg', 'jpeg', 'png', 'webp', 'JPG', 'PNG', 'JPEG'];
                foreach ($extensions as $ext) {
                    $imagePath = __DIR__ . "/Data/imgs/{$sku}.{$ext}";
                    if (file_exists($imagePath)) {
                        $file = new \Illuminate\Http\UploadedFile(
                            $imagePath,
                            "{$sku}.{$ext}",
                            mime_content_type($imagePath),
                            null,
                            true // test mode to bypass is_uploaded_file check
                        );
                        $this->productImageRepository->upload(['images' => ['files' => [$file]]], $product, 'images');
                        if (isset($this->command)) $this->command->info("Uploaded image for SKU: $sku");
                        break;
                    }
                }

            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->error("Failed to update SKU: $sku - Error: " . $e->getMessage());
            }
        }

        if (isset($this->command)) $this->command->info("Completed! Added: $addedCount, Updated: $updatedCount.");
    }

    protected function setupCategories()
    {
        $slugs = ['0-2', '3-4', '5-7', '8-10', '11-12', '13+'];
        $root = $this->categoryRepository->findOneByField('parent_id', null);
        if (!$root) {
            if (isset($this->command)) $this->command->error("Root category not found!");
            return [];
        }

        $map = [];
        foreach ($slugs as $slug) {
            $existingTranslation = \Illuminate\Support\Facades\DB::table('category_translations')->where('name', $slug)->where('locale', 'en')->first();
            if ($existingTranslation) {
                $map[$slug] = $existingTranslation->category_id;
                continue;
            }

            $category = $this->categoryRepository->create([
                'status' => 1,
                'position' => 1,
                'display_mode' => 'products_and_description',
                'parent_id' => $root->id
            ]);

            \Illuminate\Support\Facades\DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'en'],
                ['name' => $slug, 'slug' => $slug, 'description' => $slug]
            );

            \Illuminate\Support\Facades\DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'ar'],
                ['name' => $slug, 'slug' => $slug, 'description' => $slug]
            );

            $map[$slug] = $category->id;
        }
        return $map;
    }

    protected function setupGroups()
    {
        $groups = [
            ['ar' => 'العاب', 'en' => 'Toys'],
            ['ar' => 'وصل حديثا', 'en' => 'New Arrivals'],
            ['ar' => 'تعليم', 'en' => 'Educational'],
            ['ar' => 'هدايا', 'en' => 'Gifts'],
            ['ar' => 'اقل من 1 دينار', 'en' => 'Under 1 Dinar'],
            ['ar' => 'ترفيه', 'en' => 'Entertainment'],
            ['ar' => 'رياضة', 'en' => 'Sports'],
        ];

        $groupAttr = $this->attributeRepository->findOneByField('code', 'group');
        if (!$groupAttr) {
            if (isset($this->command)) $this->command->error("Group attribute not found!");
            return [];
        }

        $map = [];
        foreach ($groups as $b) {
            $existingTranslation = \Illuminate\Support\Facades\DB::table('attribute_option_translations')
                ->where('label', $b['en'])
                ->first();

            $optionId = null;
            if ($existingTranslation) {
                $optionId = $existingTranslation->attribute_option_id;
            } else {
                $option = $this->attributeOptionRepository->create([
                    'attribute_id' => $groupAttr->id,
                    'admin_name' => $b['en'],
                    'sort_order' => 1,
                ]);
                $optionId = $option->id;

                \Illuminate\Support\Facades\DB::table('attribute_option_translations')->updateOrInsert(
                    ['attribute_option_id' => $optionId, 'locale' => 'en'],
                    ['label' => $b['en']]
                );

                \Illuminate\Support\Facades\DB::table('attribute_option_translations')->updateOrInsert(
                    ['attribute_option_id' => $optionId, 'locale' => 'ar'],
                    ['label' => $b['ar']]
                );
            }

            $map[$optionId] = [$b['en'], $b['ar']];
        }

        return $map;
    }
}
