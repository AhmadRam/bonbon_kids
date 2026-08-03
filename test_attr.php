<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$attrs = Illuminate\Support\Facades\DB::table('attributes')->get(['code', 'admin_name'])->toArray();
file_put_contents('attrs.json', json_encode($attrs, JSON_PRETTY_PRINT));
