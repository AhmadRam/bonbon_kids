<?php
use Webkul\Category\Models\CategoryTranslation;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Models\Product;
use Webkul\Category\Models\Category;
use Illuminate\Support\Facades\DB;

$json = file_get_contents('/var/www/html/excel_data.json');
$data = json_decode($json, true);

if (!$data) {
    echo "Failed to load JSON data.\n";
    return;
}

$categoryTrans = CategoryTranslation::where('name', 'like', '%عروض%')->first();
if (!$categoryTrans) {
    echo "Category not found!\n";
    return;
}
$categoryId = $categoryTrans->category_id;
echo "Found category ID: $categoryId\n";

$productRepository = app(ProductRepository::class);

$count = 0;
foreach ($data as $row) {
    $sku = $row['﻿Code']; // Notice the byte order mark in '﻿Code' from json output! Wait, I should handle that.
    if (!isset($row['﻿Code']) && isset($row['Code'])) {
        $sku = $row['Code'];
    }
    
    // Let's iterate keys to find sku case-insensitively or containing 'Code'
    foreach ($row as $key => $val) {
        if (strpos($key, 'Code') !== false) {
            $sku = $val;
            break;
        }
    }

    $cost = $row['تكلفة المنتج'] ?? null;
    $price = $row['سعر بيع الموقع'] ?? null;
    $specialPrice = $row['السعر بعد الخصم'] ?? null;

    if (!$sku) continue;

    $product = Product::where('sku', $sku)->first();
    if ($product) {
        echo "Updating product SKU: $sku (ID: {$product->id})\n";
        
        $updateData = [];
        if ($cost !== null) $updateData['cost'] = $cost;
        if ($price !== null) $updateData['price'] = $price;
        if ($specialPrice !== null) {
            $updateData['special_price'] = $specialPrice;
            $updateData['special_price_status'] = 1;
        }
        $updateData['channel'] = 'default';
        $updateData['locale'] = app()->getLocale();
        $updateData['status'] = 1; // Enable the product
        $updateData['visible_individually'] = 1;
        $updateData['guest_checkout'] = 1;
        
        try {
            $productRepository->update($updateData, $product->id);
        } catch (\Exception $e) {
            echo "Error updating product {$product->id}: " . $e->getMessage() . "\n";
        }
        
        // Update category
        $currentCategories = $product->categories->pluck('id')->toArray();
        if (!in_array($categoryId, $currentCategories)) {
            $currentCategories[] = $categoryId;
            $product->categories()->sync($currentCategories);
        }
        $count++;
    } else {
        echo "Product SKU: $sku NOT FOUND!\n";
    }
}
echo "Finished updating $count products.\n";
