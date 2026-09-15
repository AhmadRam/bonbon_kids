<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Get Inventory sources
$sources = \Webkul\Inventory\Models\InventorySource::all()->toArray();
echo "--- INVENTORY SOURCES ---\n";
echo json_encode($sources, JSON_PRETTY_PRINT) . "\n";

// 2. Test Daftra Product Structure
$daftra = app('Webkul\Daftra\Http\Controllers\DaftraController');
$products = $daftra->getProducts(1, 1);
echo "--- DAFTRA PRODUCTS ---\n";
echo json_encode($products, JSON_PRETTY_PRINT) . "\n";
