<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$daftra = app('Webkul\Daftra\Http\Controllers\DaftraController');
echo json_encode($daftra->getProducts(1, 1));
