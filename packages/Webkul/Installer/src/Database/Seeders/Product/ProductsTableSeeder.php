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

        // Delete all existing products to start completely fresh
        if (isset($this->command)) {
            $this->command->info("Deleting all existing products to start completely fresh...");
        }
        $products = $this->productRepository->all();
        foreach ($products as $product) {
            try {
                $this->productRepository->delete($product->id);
            } catch (\Exception $e) {
                // Ignore
            }
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

        // 2. Build Categories Map Dynamically from DB
        if (isset($this->command)) $this->command->info("Building categories map dynamically...");
        $categoriesMap = [];
        $categories = \Illuminate\Support\Facades\DB::table('category_translations')->whereIn('locale', ['en', 'ar'])->get();
        foreach ($categories as $cat) {
            $categoriesMap[$cat->name] = $cat->category_id;
            $categoriesMap[$cat->slug] = $cat->category_id;
        }

        // 3. Build Groups Map Dynamically from DB
        if (isset($this->command)) $this->command->info("Building groups map dynamically...");
        $groupsMap = [];
        $attribute = \Illuminate\Support\Facades\DB::table('attributes')->where('code', 'group')->first();
        if ($attribute) {
            $options = \Illuminate\Support\Facades\DB::table('attribute_option_translations')
                ->join('attribute_options', 'attribute_option_translations.attribute_option_id', '=', 'attribute_options.id')
                ->where('attribute_options.attribute_id', $attribute->id)
                ->select('attribute_options.id as id', 'attribute_option_translations.label as label')
                ->get();
            foreach ($options as $opt) {
                if (!isset($groupsMap[$opt->id])) {
                    $groupsMap[$opt->id] = [];
                }
                $groupsMap[$opt->id][] = trim($opt->label);
            }
        }

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
                'new' => 1,
                'featured' => 1,
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
}
