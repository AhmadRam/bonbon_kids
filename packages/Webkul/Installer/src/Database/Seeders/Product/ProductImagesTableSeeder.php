<?php

namespace Webkul\Installer\Database\Seeders\Product;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductImageRepository;

class ProductImagesTableSeeder extends Seeder
{
    protected $productRepository;
    protected $productImageRepository;

    public function __construct(
        ProductRepository $productRepository,
        ProductImageRepository $productImageRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productImageRepository = $productImageRepository;
    }

    public function run()
    {
        Model::reguard();
        ini_set('memory_limit', '-1');

        if (isset($this->command)) {
            $this->command->info("Starting Import Product Images...");
        }

        // Prepare image map for case-insensitive matching
        $imageMap = [];
        $imageDir = __DIR__ . '/Data/imgs';
        if (is_dir($imageDir)) {
            $files = scandir($imageDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $imageMap[strtolower(trim($filename))] = $imageDir . '/' . $file;
            }
        } else {
            if (isset($this->command)) $this->command->error("Image directory not found: " . $imageDir);
            return;
        }

        $products = $this->productRepository->all();
        $uploadedCount = 0;

        foreach ($products as $product) {
            $sku = $product->sku;
            $searchSku = strtolower(trim($sku));
            if (isset($imageMap[$searchSku])) {
                $imagePath = $imageMap[$searchSku];
                if (file_exists($imagePath)) {
                    try {
                        $file = new \Illuminate\Http\UploadedFile(
                            $imagePath,
                            basename($imagePath),
                            mime_content_type($imagePath),
                            null,
                            true // test mode to bypass is_uploaded_file check
                        );
                        $this->productImageRepository->upload(['images' => ['files' => [$file]]], $product, 'images');
                        if (isset($this->command)) $this->command->info("Uploaded image for SKU: $sku");
                        $uploadedCount++;
                    } catch (\Exception $e) {
                        if (isset($this->command)) $this->command->error("Failed to upload image for SKU: $sku - Error: " . $e->getMessage());
                    }
                }
            }
        }

        if (isset($this->command)) $this->command->info("Completed! Uploaded images for $uploadedCount products.");
    }
}
