<?php

use Illuminate\Contracts\Console\Kernel;
use Webkul\Product\Repositories\ProductRepository;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
app()->setLocale('ar');
$repo = app(ProductRepository::class);
$product = $repo->findBySlug('حقيبة-الطبيبة-الصغيرة');
var_dump($product ? $product->id : 'NOT FOUND');
