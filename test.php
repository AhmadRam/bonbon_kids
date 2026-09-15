<?php

$content = file_get_contents('/var/www/html/packages/Webkul/Shop/src/Routes/store-front-routes.php');
$content = str_replace(
    'Route::fallback(ProductsCategoriesProxyController::class.\'@index\')',
    'Route::fallback(ProductsCategoriesProxyController::class.\'@index\')',
    $content
);
$content = str_replace(
    "->name('shop.product_or_category.index')\n    ->middleware('cache.response');",
    "->name('shop.product_or_category.index');",
    $content
);
file_put_contents('/var/www/html/packages/Webkul/Shop/src/Routes/store-front-routes.php', $content);
