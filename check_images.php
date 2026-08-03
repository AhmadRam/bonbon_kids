<?php
use Webkul\Product\Models\Product;

$json = file_get_contents('/var/www/html/excel_data.json');
$data = json_decode($json, true);

if (!$data) {
    echo "Failed to load JSON data.\n";
    return;
}

$results = [];

foreach ($data as $row) {
    $sku = $row['﻿Code'] ?? $row['Code'] ?? null;
    if (!$sku) {
        foreach ($row as $key => $val) {
            if (strpos($key, 'Code') !== false) {
                $sku = $val;
                break;
            }
        }
    }

    if (!$sku) continue;

    $product = Product::where('sku', $sku)->first();
    $hasImage = 'No';
    if ($product) {
        // In Bagisto, product images are related via 'images'
        if ($product->images && $product->images->count() > 0) {
            $hasImage = 'Yes';
        }
    }
    
    $row['يوجد صور (Yes/No)'] = $hasImage;
    $results[] = $row;
}

file_put_contents('/var/www/html/excel_data_with_images.json', json_encode($results, JSON_UNESCAPED_UNICODE));
echo "Successfully checked images for " . count($results) . " products.\n";
