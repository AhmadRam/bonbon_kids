<?php

use Illuminate\Contracts\Console\Kernel;
use Webkul\Product\Models\Product;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$p = Product::find(66);
var_dump($p->visible_individually, $p->status);
