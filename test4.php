<?php

use Illuminate\Contracts\Console\Kernel;
use Webkul\Product\Repositories\ElasticSearchRepository;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
app()->setLocale('ar');
$repo = app(ElasticSearchRepository::class);
$indices = $repo->search(['url_key' => 'حقيبة-الطبيبة-الصغيرة'], ['type' => '', 'from' => 0, 'limit' => 1, 'sort' => 'id', 'order' => 'desc']);
print_r($indices);
