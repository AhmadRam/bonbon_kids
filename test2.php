<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$products = DB::table('product_flat')->where('url_key', 'like', '%الطبيبة%')->get(['id', 'product_id', 'url_key', 'locale']);
print_r($products->toArray());
