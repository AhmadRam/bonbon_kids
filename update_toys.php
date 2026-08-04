<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;

$json = file_get_contents(__DIR__.'/update_toys.json');
$data = json_decode($json, true);

if (!$data) {
    die("Error parsing JSON data\n");
}

$productRepository = app(ProductRepository::class);
$inventoryRepository = app(ProductInventoryRepository::class);

$count = 0;
foreach ($data as $item) {
    $sku = trim((string)$item['sku']);
    $qty = (int)$item['qty'];
    $cost = (float)$item['cost'];
    $price = (float)$item['price'];

    if (empty($sku)) continue;

    $product = Product::where('sku', $sku)->first();
    
    if (!$product) {
        echo "SKU NOT FOUND: $sku\n";
        continue;
    }

    $updateData = [];
    $updateAttrs = [];
    
    if ($price > 0) {
        $updateData['price'] = $price;
        $updateAttrs[] = 'price';
    }
    if ($cost > 0) {
        $updateData['cost'] = $cost;
        $updateAttrs[] = 'cost';
    }

    if (!empty($updateAttrs)) {
        $productRepository->update($updateData, $product->id, $updateAttrs);
    }

    // Update Inventory
    $inventorySource = DB::table('inventory_sources')->where('status', 1)->first();
    if ($inventorySource) {
        $sourceId = $inventorySource->id;
        $inventoryRepository->saveInventories([
            'inventories' => [$sourceId => $qty],
            'vendor_id' => 0
        ], $product);
    }
    
    $count++;
}
echo "Successfully updated $count products.\n";
