<?php

$content = file_get_contents('/var/www/html/packages/Webkul/Shop/src/Http/Controllers/ProductsCategoriesProxyController.php');
$content = str_replace('abort(404);', 'throw new \Exception("Aborted here: " . $slugOrURLKey);', $content);
file_put_contents('/var/www/html/packages/Webkul/Shop/src/Http/Controllers/ProductsCategoriesProxyController.php', $content);
