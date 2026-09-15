<?php

$content = file_get_contents('/var/www/html/packages/Webkul/Shop/src/Http/Controllers/ProductsCategoriesProxyController.php');
$content = str_replace('public function index(Request $request)', 'public function index(Request $request) { throw new \Exception("HIT FALLBACK: " . $request->getPathInfo());', $content);
file_put_contents('/var/www/html/packages/Webkul/Shop/src/Http/Controllers/ProductsCategoriesProxyController.php', $content);
