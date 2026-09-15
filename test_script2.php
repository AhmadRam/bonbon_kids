<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$daftra = app('Webkul\Daftra\Http\Controllers\DaftraController');
$products = $daftra->getProducts(1, 100);
foreach($products as $p) {
    if(isset($p['Product']['stock_balance']) && $p['Product']['stock_balance'] > 0) {
        echo "Found Product with stock:\n";
        echo json_encode($p, JSON_PRETTY_PRINT) . "\n";
        break;
    }
}
